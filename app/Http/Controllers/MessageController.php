<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Message;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $sort = $request->get('sort', 'created_at');
        $direction = $request->get('direction', 'desc');
        
        $query = Message::whereNull('parent_id')
            ->where(function($query) use ($user) {
                // Mensajes recibidos directamente
                $query->where(function($q) use ($user) {
                    $q->where('receiver_id', $user->id)
                      ->where('deleted_by_receiver', false);
                })
                // O mensajes enviados que tienen al menos una respuesta del otro usuario
                ->orWhere(function($q) use ($user) {
                    $q->where('sender_id', $user->id)
                      ->where('deleted_by_sender', false)
                      ->whereHas('replies', function($rq) use ($user) {
                          $rq->where('receiver_id', $user->id);
                      });
                });
            })
            ->with(['sender', 'receiver', 'replies'])
            ->withCount(['replies as unread_replies_count' => function($q) use ($user) {
                $q->where('receiver_id', $user->id)->whereNull('read_at');
            }]);

        if ($request->get('filter') === 'unread') {
            $query->where(function($q) use ($user) {
                $q->whereNull('read_at')
                  ->orWhereHas('replies', function($rq) use ($user) {
                      $rq->where('receiver_id', $user->id)->whereNull('read_at');
                  });
            });
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                  ->orWhere('body', 'like', "%{$search}%")
                  ->orWhereHas('sender', function($sq) use ($search) {
                      $sq->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('receiver', function($rq) use ($search) {
                      $rq->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('replies', function($rq) use ($search) {
                      $rq->where('body', 'like', "%{$search}%")
                        ->orWhereHas('sender', function($rsq) use ($search) {
                            $rsq->where('name', 'like', "%{$search}%");
                        });
                  });
            });
        }

        if ($sort === 'status') {
            // Ordenar por estado: No leídos primero (read_at es null) o viceversa
            // También consideramos unread_replies_count
            if ($direction === 'desc') {
                $query->orderByRaw('CASE WHEN read_at IS NULL OR unread_replies_count > 0 THEN 0 ELSE 1 END')
                      ->latest();
            } else {
                $query->orderByRaw('CASE WHEN read_at IS NULL OR unread_replies_count > 0 THEN 1 ELSE 0 END')
                      ->latest();
            }
        } else {
            $query->orderBy($sort, $direction);
        }

        $messages = $query->paginate(10)->withQueryString();

        return view('messages.index', compact('messages'));
    }

    public function sent(Request $request)
    {
        $sort = $request->get('sort', 'created_at');
        $direction = $request->get('direction', 'desc');

        $query = Auth::user()->sentMessages()
            ->where('deleted_by_sender', false)
            ->whereNull('parent_id')
            ->with('receiver');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                  ->orWhere('body', 'like', "%{$search}%")
                  ->orWhereHas('receiver', function($rq) use ($search) {
                      $rq->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('replies', function($rq) use ($search) {
                      $rq->where('body', 'like', "%{$search}%")
                        ->orWhereHas('receiver', function($rrq) use ($search) {
                            $rrq->where('name', 'like', "%{$search}%");
                        });
                  });
            });
        }

        if ($sort === 'status') {
            $query->orderBy('read_at', $direction === 'desc' ? 'asc' : 'desc');
        } else {
            $query->orderBy($sort, $direction);
        }

        if ($request->get('filter') === 'unread') {
            $query->whereNull('read_at');
        }

        $messages = $query->paginate(10)->withQueryString();

        return view('messages.sent', compact('messages'));
    }

    public function show(Message $message)
    {
        // Si el mensaje es una respuesta, redirigir al mensaje raíz
        if ($message->parent_id) {
            return redirect()->route('messages.show', $message->parent_id);
        }

        // Verificar que el usuario sea el remitente o el destinatario del mensaje raíz o de cualquier respuesta
        // Para simplificar, verificamos el mensaje raíz
        if ($message->sender_id !== Auth::id() && $message->receiver_id !== Auth::id()) {
            abort(403);
        }

        // Cargar respuestas
        $message->load(['replies.sender', 'replies.receiver']);

        // Marcar como leído el mensaje principal si el usuario es el destinatario 
        // o si es el remitente pero el mensaje está marcado como "no leído" (indicando novedad)
        if (($message->receiver_id === Auth::id() || $message->sender_id === Auth::id()) && is_null($message->read_at)) {
            $message->update(['read_at' => now()]);
        }

        // Marcar como leídas las respuestas dirigidas al usuario
        foreach ($message->replies as $reply) {
            if ($reply->receiver_id === Auth::id() && is_null($reply->read_at)) {
                $reply->update(['read_at' => now()]);
            }
        }

        return view('messages.show', compact('message'));
    }

    public function create()
    {
        $users = \App\Models\User::where('id', '!=', Auth::id())->get();
        $roles = \Spatie\Permission\Models\Role::all();
        $groups = \App\Models\Group::all();
        return view('messages.create', compact('users', 'roles', 'groups'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject' => 'nullable|string|max:255',
            'body' => 'required|string',
            'parent_id' => 'nullable|exists:messages,id',
            'receiver_id' => 'required_without_all:role_id,group_id|nullable|exists:users,id',
            'role_id' => 'nullable|exists:roles,id',
            'group_id' => 'nullable|exists:groups,id',
        ]);

        $sender_id = Auth::id();
        $recipientIds = [];

        if ($request->filled('parent_id')) {
            $recipientIds[] = $request->receiver_id;
        } elseif ($request->filled('role_id')) {
            $role = \Spatie\Permission\Models\Role::find($request->role_id);
            $recipientIds = \App\Models\User::role($role->name)->where('id', '!=', $sender_id)->pluck('id')->toArray();
        } elseif ($request->filled('group_id')) {
            $recipientIds = \App\Models\User::where('group_id', $request->group_id)->where('id', '!=', $sender_id)->pluck('id')->toArray();
        } else {
            $recipientIds[] = $request->receiver_id;
        }

        $recipientIds = array_filter($recipientIds);

        if (empty($recipientIds)) {
            return redirect()->back()->with('error', 'No se encontraron destinatarios. Asegúrate de seleccionar uno.');
        }

        foreach ($recipientIds as $recipientId) {
            $msgData = [
                'sender_id' => $sender_id,
                'receiver_id' => $recipientId,
                'subject' => $request->subject ?: '(Sin asunto)',
                'body' => $request->body,
                'parent_id' => $request->parent_id,
            ];
            
            $newMessage = Message::create($msgData);
            
            // Dispatch real-time notification
            event(new \App\Events\MessageSent($newMessage));

            if ($request->filled('parent_id')) {
                $parent = Message::find($request->parent_id);
                // Restore thread for participants if it was deleted
                $parent->update([
                    'read_at' => null,
                    'deleted_by_sender' => false,
                    'deleted_by_receiver' => false
                ]);
            }
        }

        if ($request->filled('parent_id')) {
            return redirect()->route('messages.show', $request->parent_id)->with('success', 'Respuesta enviada correctamente.');
        }

        $count = count($recipientIds);
        $msg = $count === 1 ? 'Mensaje enviado correctamente.' : "Se han enviado {$count} mensajes.";
        return redirect()->route('messages.sent')->with('success', $msg);
    }

    public function destroy(Message $message)
    {
        $userId = Auth::id();

        // Si el usuario es el remitente
        if ($message->sender_id === $userId) {
            $message->update(['deleted_by_sender' => true]);
            // También marcar todas las respuestas como borradas por el remitente
            $message->replies()->update(['deleted_by_sender' => true]);
        }

        // Si el usuario es el destinatario
        if ($message->receiver_id === $userId) {
            $message->update(['deleted_by_receiver' => true]);
            // También marcar todas las respuestas como borradas por el destinatario
            $message->replies()->update(['deleted_by_receiver' => true]);
        }

        // Opcional: Si ambos lo borraron, eliminar de la DB (incluyendo cascada de respuestas)
        if ($message->deleted_by_sender && $message->deleted_by_receiver) {
            $message->delete();
        }

        return redirect()->back()->with('success', 'Mensaje eliminado de tu bandeja.');
    }

    public function toggleUnread(Message $message)
    {
        // Cualquier participante del hilo puede marcar como leído/no leído
        if ($message->receiver_id !== Auth::id() && $message->sender_id !== Auth::id()) {
            abort(403);
        }

        if (is_null($message->read_at)) {
            $message->update(['read_at' => now()]);
            $msg = 'Mensaje marcado como leído.';
        } else {
            $message->update(['read_at' => null]);
            $msg = 'Mensaje marcado como no leído.';
        }

        return redirect()->back()->with('success', $msg);
    }

    public function bulkAction(Request $request)
    {
        $ids = $request->input('message_ids', []);
        $action = $request->input('action');

        if (empty($ids) || !$action) {
            return redirect()->back()->with('error', 'Debes seleccionar al menos un mensaje y una acción para continuar.');
        }

        $messages = Message::whereIn('id', $ids)->get();
        $user = Auth::user();

        foreach ($messages as $message) {
            if ($message->sender_id !== $user->id && $message->receiver_id !== $user->id) {
                continue;
            }

            if ($action === 'delete') {
                if ($message->sender_id == $user->id) {
                    $message->update(['deleted_by_sender' => true]);
                    $message->replies()->update(['deleted_by_sender' => true]);
                }
                if ($message->receiver_id == $user->id) {
                    $message->update(['deleted_by_receiver' => true]);
                    $message->replies()->update(['deleted_by_receiver' => true]);
                }
                if ($message->deleted_by_sender && $message->deleted_by_receiver) {
                    $message->delete();
                }
            } elseif ($action === 'read' && $message->receiver_id == $user->id) {
                $message->update(['read_at' => now()]);
                // Also mark replies as read for the current user
                $message->replies()->where('receiver_id', $user->id)->update(['read_at' => now()]);
            } elseif ($action === 'unread' && $message->receiver_id == $user->id) {
                $message->update(['read_at' => null]);
                // Also mark replies as unread for the current user (optional, but consistent)
                $message->replies()->where('receiver_id', $user->id)->update(['read_at' => null]);
            }
        }

        $count = count($ids);
        $msg = '';
        if ($action === 'delete') {
            $msg = $count === 1 ? 'Mensaje eliminado correctamente.' : "{$count} mensajes eliminados correctamente.";
        } elseif ($action === 'read') {
            $msg = $count === 1 ? 'Mensaje marcado como leído.' : "{$count} mensajes marcados como leídos.";
        } elseif ($action === 'unread') {
            $msg = $count === 1 ? 'Mensaje marcado como no leído.' : "{$count} mensajes marcados como no leídos.";
        }

        return redirect()->back()->with('success', $msg ?: 'Acción realizada correctamente.');
    }
}

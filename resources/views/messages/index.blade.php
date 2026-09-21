@extends('layouts.app')

@section('title', 'Mensajes Recibidos')

@section('content')
<div class="content-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <div>
        <h1 style="font-size: 1.875rem; font-weight: 700; color: #fff; margin: 0;">Mensajes Recibidos</h1>
        <p style="color: var(--text-muted); margin: 0.25rem 0 0 0;">Bandeja de entrada</p>
    </div>
    <div style="display: flex; gap: 0.75rem;">
        <a href="{{ route('messages.create') }}" class="btn btn-primary">
            Nuevo Mensaje
        </a>
        <a href="{{ route('messages.sent') }}" class="btn btn-secondary">
            Enviados
        </a>
    </div>
</div>

<!-- Filters and Search (Search remains separate) -->
<div style="margin-bottom: 1.5rem; display: flex; justify-content: flex-end; align-items: center; gap: 1rem;">
    <form action="{{ request()->url() }}" method="GET" style="display: flex; gap: 0.5rem; flex: 1; max-width: 450px;">
        @if(request('sort')) <input type="hidden" name="sort" value="{{ request('sort') }}"> @endif
        @if(request('direction')) <input type="hidden" name="direction" value="{{ request('direction') }}"> @endif
        @if(request('filter')) <input type="hidden" name="filter" value="{{ request('filter') }}"> @endif
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar mensajes..." class="form-control" style="flex: 1; padding: 0.5rem 1rem; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.1); border-radius: 0.75rem; color: #fff; font-size: 0.9rem;">
        <button type="submit" class="btn btn-secondary" style="padding: 0 1rem;">🔍</button>
        @if(request('search'))
            <a href="{{ request()->url() }}" class="btn btn-secondary" style="padding: 0 1rem; display: flex; align-items: center; justify-content: center; text-decoration: none; background: rgba(255,255,255,0.05); font-size: 0.8rem; font-weight: 500;" title="Limpiar búsqueda">Limpiar</a>
        @endif
    </form>
</div>

<div class="card" style="padding: 1.5rem;">
    <form id="bulk-form" action="{{ route('messages.bulk-action') }}" method="POST">
        @csrf
        <input type="hidden" name="action" id="bulk-action-input">
        
        <!-- Bulk Actions Header -->
        <div style="margin-bottom: 1rem; display: flex; justify-content: space-between; align-items: center; min-height: 40px;">
            <div style="display: flex; gap: 0.5rem; align-items: center;">
                <a href="{{ request()->fullUrlWithQuery(['filter' => request('filter') === 'unread' ? null : 'unread']) }}" 
                   class="btn {{ request('filter') === 'unread' ? 'btn-primary' : 'btn-secondary' }}" 
                   style="font-size: 0.8rem; padding: 0.5rem 1rem;">
                   {{ request('filter') === 'unread' ? '✕ Ver todos' : '📥 Solo no leídos' }}
                </a>

                <div id="bulk-actions" style="display: none; gap: 0.5rem; align-items: center; background: rgba(56, 189, 248, 0.1); padding: 0.25rem 1rem; border-radius: 0.75rem; border: 1px solid rgba(56, 189, 248, 0.2);">
                    <span style="font-size: 0.8rem; font-weight: 600; color: var(--primary); margin-right: 0.5rem;" id="selected-count">0 seleccionados</span>
                    <button type="button" id="btn-mark-read" class="btn btn-secondary btn-bulk" data-action="read" style="font-size: 0.75rem; padding: 0.4rem 0.8rem; background: transparent;">Marcar leído</button>
                    <button type="button" id="btn-mark-unread" class="btn btn-secondary btn-bulk" data-action="unread" style="font-size: 0.75rem; padding: 0.4rem 0.8rem; background: transparent;">Marcar no leído</button>
                    <button type="button" id="btn-bulk-delete" class="btn btn-secondary btn-bulk" data-action="delete" style="font-size: 0.75rem; padding: 0.4rem 0.8rem; background: transparent; color: #ef4444;">Borrar</button>
                </div>
            </div>
        </div>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 40px;">
                            <input type="checkbox" id="select-all" style="cursor: pointer; width: 18px; height: 18px; accent-color: var(--primary);">
                        </th>
                        <th>Remitente</th>
                        <th>Asunto</th>
                        <th>
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'created_at', 'direction' => request('sort') == 'created_at' && request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="sortable-header">
                                <span>Fecha</span>
                                <span class="sort-indicator {{ request('sort') == 'created_at' ? 'active' : '' }}">
                                    {!! request('sort') == 'created_at' ? (request('direction') == 'asc' ? '▲' : '▼') : '↕' !!}
                                </span>
                            </a>
                        </th>
                        <th>
                            <a href="{{ request()->fullUrlWithQuery(['sort' => 'status', 'direction' => request('sort') == 'status' && request('direction') == 'asc' ? 'desc' : 'asc']) }}" class="sortable-header">
                                <span>Estado</span>
                                <span class="sort-indicator {{ request('sort') == 'status' ? 'active' : '' }}">
                                    {!! request('sort') == 'status' ? (request('direction') == 'asc' ? '▲' : '▼') : '↕' !!}
                                </span>
                            </a>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($messages as $message)
                    @php
                        $isUnread = (is_null($message->read_at) && ($message->receiver_id === Auth::id() || $message->sender_id === Auth::id())) 
                                    || ($message->unread_replies_count > 0);
                        $otherUser = $message->sender_id === Auth::id() ? $message->receiver : $message->sender;
                    @endphp
                    <tr class="clickable-row {{ $isUnread ? 'unread-row' : '' }}" onclick="if(!event.target.closest('input')) window.location='{{ route('messages.show', $message) }}'" style="{{ $isUnread ? 'background: rgba(56, 189, 248, 0.08);' : '' }}">
                        <td onclick="event.stopPropagation()">
                            <input type="checkbox" name="message_ids[]" value="{{ $message->id }}" class="message-checkbox" 
                                   data-is-unread="{{ $isUnread ? 'true' : 'false' }}"
                                   style="cursor: pointer; width: 18px; height: 18px; accent-color: var(--primary);">
                        </td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 0.75rem;">
                                <div style="width: 32px; height: 32px; border-radius: 50%; background: {{ $isUnread ? 'var(--primary)' : '#4b5563' }}; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 0.8rem; font-weight: 700; flex-shrink: 0;">
                                    {{ strtoupper(substr($otherUser->name, 0, 1)) }}
                                </div>
                                <span style="{{ $isUnread ? 'font-weight: 700; color: #fff;' : '' }}">{{ $otherUser->name }}</span>
                            </div>
                        </td>
                        <td>
                            <div style="display: flex; flex-direction: column;">
                                <span style="{{ $isUnread ? 'font-weight: 700; color: #fff;' : '' }}">{{ $message->subject }}</span>
                                <span style="font-size: 0.75rem; color: var(--text-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 300px;">
                                    {{ Str::limit(strip_tags($message->body), 60) }}
                                </span>
                            </div>
                        </td>
                        <td style="{{ $isUnread ? 'color: rgba(255,255,255,0.9);' : '' }}">{{ $message->created_at->diffForHumans() }}</td>
                        <td>
                            @if($isUnread)
                                <span class="status-badge" style="background: var(--primary); color: #fff; padding: 0.2rem 0.6rem; border-radius: 0.5rem; font-size: 0.7rem; font-weight: 700;">Nuevo</span>
                            @else
                                <span class="status-badge" style="background: rgba(255, 255, 255, 0.05); color: var(--text-muted); padding: 0.2rem 0.6rem; border-radius: 0.5rem; font-size: 0.7rem;">Leído</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 3rem; color: var(--text-muted);">
                            No hay mensajes que coincidan con los criterios.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </form>
    <div style="margin-top: 1.5rem;">
        {{ $messages->links() }}
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Inbox: Initializing scripts');
        const selectAll = document.getElementById('select-all');
        const checkboxes = document.querySelectorAll('.message-checkbox');
        const bulkActions = document.getElementById('bulk-actions');
        const selectedCount = document.getElementById('selected-count');
        const bulkForm = document.getElementById('bulk-form');
        const bulkActionInput = document.getElementById('bulk-action-input');

        const btnMarkRead = document.getElementById('btn-mark-read');
        const btnMarkUnread = document.getElementById('btn-mark-unread');
        const bulkButtons = document.querySelectorAll('.btn-bulk');

        if (!bulkForm) {
            console.error('Inbox Error: bulk-form not found');
            return;
        }

        function updateBulkActions() {
            const checkedBoxes = document.querySelectorAll('.message-checkbox:checked');
            const checkedCount = checkedBoxes.length;
            
            if (checkedCount > 0) {
                bulkActions.style.display = 'flex';
                selectedCount.textContent = checkedCount + (checkedCount === 1 ? ' seleccionado' : ' seleccionados');
                
                let hasUnread = false;
                let hasRead = false;
                
                checkedBoxes.forEach(cb => {
                    if (cb.dataset.isUnread === 'true') {
                        hasUnread = true;
                    } else {
                        hasRead = true;
                    }
                });
                
                if (btnMarkRead) btnMarkRead.style.display = hasUnread ? 'block' : 'none';
                if (btnMarkUnread) btnMarkUnread.style.display = hasRead ? 'block' : 'none';
            } else {
                bulkActions.style.display = 'none';
            }
        }

        if (selectAll) {
            selectAll.addEventListener('change', function() {
                checkboxes.forEach(cb => {
                    cb.checked = selectAll.checked;
                });
                updateBulkActions();
            });
        }

        checkboxes.forEach(cb => {
            cb.addEventListener('change', updateBulkActions);
        });

        bulkButtons.forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const action = this.getAttribute('data-action');
                console.log('Inbox: Action requested:', action);
                
                const checkedCount = document.querySelectorAll('.message-checkbox:checked').length;
                if (checkedCount === 0) {
                    alert('Debes seleccionar al menos un mensaje.');
                    return;
                }

                let confirmMsg = '¿Realizar esta acción en los mensajes seleccionados?';
                if (action === 'delete') confirmMsg = '¿Eliminar definitivamente los mensajes seleccionados?';
                
                if (confirm(confirmMsg)) {
                    console.log('Inbox: Submitting form with action:', action);
                    bulkActionInput.value = action;
                    bulkForm.submit();
                } else {
                    console.log('Inbox: Action cancelled by user');
                }
            });
        });
    });
</script>
@endpush

<style>
    .unread-row:hover {
        background: rgba(56, 189, 248, 0.12) !important;
    }
</style>
@endsection

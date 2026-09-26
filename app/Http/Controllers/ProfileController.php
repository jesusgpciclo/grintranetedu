<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function edit()
    {
        $user = auth()->user();
        $isAlumno = $user->hasRole('alumno');
        $isTeacherOrStaff = !$isAlumno;
        
        $departamentos = [
            'Biología y Geología',
            'Física y Química',
            'Matemáticas',
            'Lengua Castellana y Literatura',
            'Geografía e Historia',
            'Inglés',
            'Francés',
            'Educación Física',
            'Música',
            'Dibujo y Artes Plásticas',
            'Tecnología e Informática',
            'Filosofía',
            'Economía',
            'Orientación Educativa',
            'Formación y Orientación Laboral (FOL)',
            'Informática y Comunicaciones',
            'Religión',
            'Otros'
        ];

        $predefinedAvatars = [
            'avatars/predefined/teacher_1.svg',
            'avatars/predefined/teacher_2.svg',
            'avatars/predefined/teacher_3.svg',
            'avatars/predefined/teacher_4.svg',
            'avatars/predefined/teacher_5.svg',
            'avatars/predefined/teacher_6.svg',
            'avatars/predefined/teacher_7.svg',
            'avatars/predefined/teacher_8.svg',
        ];

        // Docentes titulares disponibles para vinculación de sustitutos
        $rolesToCheck = \Spatie\Permission\Models\Role::whereIn('name', ['profesor', 'directiva', 'director', 'admin'])->pluck('name')->toArray();
        $titulares = !empty($rolesToCheck)
            ? User::role($rolesToCheck)->where('id', '!=', $user->id)->orderBy('name')->get()
            : User::where('id', '!=', $user->id)->orderBy('name')->get();

        return view('profile.edit', compact('user', 'isAlumno', 'isTeacherOrStaff', 'departamentos', 'predefinedAvatars', 'titulares'));
    }

    public function update(Request $request)
    {
        $user = auth()->user();
        $isAlumno = $user->hasRole('alumno');

        if ($isAlumno) {
            // Alumnos can only change their password
            $request->validate([
                'password' => ['required', 'confirmed', Password::defaults()],
            ]);

            $user->update([
                'password' => Hash::make($request->password),
                'must_change_password' => false,
            ]);
        } else {
            // Other users can update more information
            $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'last_name' => ['nullable', 'string', 'max:255'],
                'departamento' => ['nullable', 'string', 'max:255'],
                'titular_user_id' => ['nullable', 'exists:users,id'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
                'password' => [$user->must_change_password ? 'required' : 'nullable', 'confirmed', Password::defaults()],
                'avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
                'predefined_avatar' => ['nullable', 'string'],
            ]);

            $data = [
                'name' => $request->name,
                'last_name' => $request->last_name,
                'departamento' => $request->departamento,
                'email' => $request->email,
            ];

            // Solo directiva o admin puede vincular o editar titular_user_id
            if ($user->hasRole('admin') || $user->hasRole('directiva')) {
                $data['titular_user_id'] = $request->titular_user_id ?: null;
            }

            if ($request->hasFile('avatar')) {
                // Delete old custom avatar if exists
                if ($user->avatar && !str_starts_with($user->avatar, 'avatars/predefined/')) {
                    Storage::disk('public')->delete($user->avatar);
                }
                
                $path = $request->file('avatar')->store('avatars', 'public');
                $data['avatar'] = $path;
            } elseif ($request->filled('predefined_avatar')) {
                $data['avatar'] = $request->predefined_avatar;
            }

            if ($request->filled('password')) {
                $data['password'] = Hash::make($request->password);
                $data['must_change_password'] = false;
            }

            $user->update($data);
        }

        return back()->with('status', 'Perfil actualizado con éxito.');
    }
}

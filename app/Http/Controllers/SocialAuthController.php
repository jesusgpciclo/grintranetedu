<?php

namespace App\Http\Controllers;

use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
            
            $user = User::where('google_id', $googleUser->id)
                        ->orWhere('email', $googleUser->email)
                        ->first();

            if ($user) {
                $updateData = [];
                // Si el usuario existe, actualizamos su google_id si no lo tiene
                if (!$user->google_id) {
                    $updateData['google_id'] = $googleUser->id;
                    $updateData['last_name'] = $googleUser->user['family_name'] ?? $user->last_name;
                    $updateData['avatar'] = $googleUser->avatar;
                }
                // Si accede con cuenta de Google, no se le exige cambiar contraseña local
                if ($user->must_change_password) {
                    $updateData['must_change_password'] = false;
                }
                if (!empty($updateData)) {
                    $user->update($updateData);
                }
                Auth::login($user);
            } else {
                // Si no existe, lo creamos
                $newUser = User::create([
                    'name' => $googleUser->user['given_name'] ?? $googleUser->name,
                    'last_name' => $googleUser->user['family_name'] ?? null,
                    'email' => $googleUser->email,
                    'google_id' => $googleUser->id,
                    'avatar' => $googleUser->avatar,
                    'password' => Hash::make(Str::random(24)), // Password aleatorio
                    'must_change_password' => false,
                ]);

                // Asignar rol por defecto (ej. alumno)
                $newUser->assignRole('alumno');

                Auth::login($newUser);
            }

            return redirect()->intended('dashboard');

        } catch (Exception $e) {
            return redirect()->route('login')->with('error', 'Algo salió mal al iniciar sesión con Google.');
        }
    }
}

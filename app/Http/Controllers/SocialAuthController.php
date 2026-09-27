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
                $isFirstGoogleLogin = empty($user->google_id);
                $updateData = [];

                // Si el usuario existe, vinculamos su google_id si es su primera vez
                if ($isFirstGoogleLogin) {
                    $updateData['google_id'] = $googleUser->id;
                    $updateData['last_name'] = $googleUser->user['family_name'] ?? $user->last_name;
                    $updateData['avatar'] = $googleUser->avatar;
                }

                // Si el usuario tiene una contraseña inicial por defecto y entra con Google,
                // se le exige establecer/cambiar su contraseña
                $hasDefaultPassword = Hash::check('profesor', $user->password)
                    || Hash::check('alumno1234', $user->password)
                    || (!empty($user->email) && Hash::check($user->email, $user->password));

                if ($hasDefaultPassword) {
                    $updateData['must_change_password'] = true;
                }

                // NOTA: Si el usuario ya tenía must_change_password = true, se preserva en true.
                // Ya no se desactiva al iniciar sesión con Google.

                if (!empty($updateData)) {
                    $user->update($updateData);
                }
                Auth::login($user);
            } else {
                // Si no existe, lo creamos con la obligación de poner una contraseña al entrar por primera vez
                $newUser = User::create([
                    'name' => $googleUser->user['given_name'] ?? $googleUser->name,
                    'last_name' => $googleUser->user['family_name'] ?? null,
                    'email' => $googleUser->email,
                    'google_id' => $googleUser->id,
                    'avatar' => $googleUser->avatar,
                    'password' => Hash::make(Str::random(24)), // Password aleatorio inicial
                    'must_change_password' => true,
                ]);

                // Asignar rol por defecto (ej. alumno)
                $newUser->assignRole('alumno');

                Auth::login($newUser);
            }

            if (Auth::user()->must_change_password) {
                return redirect()->route('profile.edit')
                    ->with('warning', 'Por motivos de seguridad, debes establecer una contraseña para tu cuenta antes de continuar.');
            }

            return redirect()->intended(route(Auth::user()->getDefaultHomeRoute()));

        } catch (Exception $e) {
            return redirect()->route('login')->with('error', 'Algo salió mal al iniciar sesión con Google.');
        }
    }
}

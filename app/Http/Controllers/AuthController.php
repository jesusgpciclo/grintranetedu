<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function loginView()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            $user = Auth::user();

            // Detect initial/default password or forced change flag
            $isInitialPassword = ($credentials['password'] === 'profesor')
                || (strtolower(trim($credentials['password'])) === strtolower(trim($user->email)))
                || ($credentials['password'] === 'alumno1234');

            if ($isInitialPassword || $user->must_change_password) {
                if (!$user->must_change_password) {
                    $user->update(['must_change_password' => true]);
                }
                return redirect()->route('profile.edit')
                    ->with('warning', 'Por motivos de seguridad, debes cambiar tu contraseña inicial antes de continuar.');
            }

            return redirect()->intended('dashboard');
        }

        return back()->withErrors([
            'email' => 'Las credenciales proporcionadas no coinciden con nuestros registros.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}

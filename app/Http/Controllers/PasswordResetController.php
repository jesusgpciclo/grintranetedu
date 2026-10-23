<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Mail\ResetPasswordMail;



class PasswordResetController extends Controller
{
    public function showLinkRequestForm()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $genericStatus = 'Si el correo electrónico existe en nuestra base de datos, hemos enviado un enlace de restablecimiento de contraseña.';

        $user = User::where('email', $request->email)->first();

        if ($user) {
            $token = Str::random(64);

            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $request->email],
                [
                    'email' => $request->email,
                    'token' => Hash::make($token),
                    'created_at' => Carbon::now()
                ]
            );

            $resetLink = route('password.reset', ['token' => $token, 'email' => $request->email]);
            
            // Send the email via configured mailer
            Mail::to($request->email)->send(new ResetPasswordMail($resetLink));
        }
        
        return back()->with('status', $genericStatus);
    }

    public function showResetForm(Request $request, $token)
    {
        return view('auth.reset-password', ['token' => $token, 'email' => $request->email]);
    }

    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:8',
        ]);

        $reset = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        $isExpired = false;
        if ($reset && $reset->created_at) {
            $isExpired = Carbon::parse($reset->created_at)->addMinutes(60)->isPast();
        }

        if (!$reset || $isExpired || !Hash::check($request->token, $reset->token)) {
            return back()->withErrors(['email' => 'Este enlace de restablecimiento de contraseña no es válido o ha caducado.']);
        }

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return redirect()->route('login');
        }

        $user->forceFill([
            'password' => Hash::make($request->password),
            'must_change_password' => false,
        ])->setRememberToken(Str::random(60));

        $user->save();

        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return redirect()->route('login')->with('status', '¡Su contraseña ha sido restablecida!');
    }
}

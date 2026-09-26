<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordIsChanged
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && Auth::user()->must_change_password && empty(Auth::user()->google_id)) {
            $allowedRoutes = ['profile.edit', 'profile.update', 'logout'];
            $currentRoute = $request->route() ? $request->route()->getName() : null;

            if (!in_array($currentRoute, $allowedRoutes) && !$request->is('logout') && !$request->is('profile*')) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'Debes cambiar tu contraseña inicial antes de continuar.',
                        'redirect' => route('profile.edit'),
                    ], 403);
                }

                return redirect()->route('profile.edit')
                    ->with('warning', 'Por motivos de seguridad, debes cambiar tu contraseña inicial antes de continuar.');
            }
        }

        return $next($request);
    }
}

@extends('layouts.app')

@section('title', 'Login - GR Intranet EDU')

@section('content')
    <div class="login-page" style="min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 1.5rem; background: radial-gradient(circle at top right, var(--primary-light), transparent), radial-gradient(circle at bottom left, var(--bg-hover), transparent);">
        <div class="card login-card" style="width: 100%; max-width: 440px; position: relative;">

            <!-- Quick Theme Switcher on Login Page -->
            <div style="position: absolute; top: 1.25rem; right: 1.25rem;">
                <button type="button" onclick="toggleTheme()" class="theme-toggle-btn" style="padding: 0.35rem 0.6rem;" title="Cambiar modo claro / oscuro">
                    <svg class="theme-sun-icon w-4 h-4 text-amber-400 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <svg class="theme-moon-icon w-4 h-4 text-sky-400 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                    </svg>
                </button>
            </div>

            <div style="text-align: center; margin-bottom: 2rem; margin-top: 0.5rem;">
                <h1 style="font-size: 2.25rem; font-weight: 800; color: var(--primary); letter-spacing: -0.03em;">
                    GR<span style="color: var(--text-heading)"> Intranet</span><span style="color: var(--primary)"> EDU</span>
                </h1>
                <p style="color: var(--text-muted); font-size: 0.95rem; margin-top: 0.25rem;">Bienvenido de nuevo</p>
            </div>

            @if ($errors->any())
                <div class="alert alert-error">
                    <ul style="margin: 0; padding-left: 1.25rem;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (session('status'))
                <div class="alert alert-success">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="form-group">
                    <label for="email">Correo Electrónico</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus placeholder="tu@email.com">
                </div>

                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <input type="password" id="password" name="password" required placeholder="••••••••">
                </div>

                <div class="form-group" style="display: flex; align-items: center; justify-content: space-between; font-size: 0.875rem;">
                    <label style="display: flex; align-items: center; gap: 0.5rem; margin: 0; cursor: pointer; color: var(--text-muted);">
                        <input type="checkbox" name="remember" style="width: auto;">
                        Recordarme
                    </label>
                    <a href="{{ route('password.request') }}" style="font-weight: 600; text-decoration: none;">
                        ¿Olvidaste tu contraseña?
                    </a>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 0.85rem; font-size: 1rem; border-radius: 0.75rem;">
                    Iniciar Sesión
                </button>
            </form>

            <div style="margin: 1.5rem 0; display: flex; align-items: center; gap: 1rem; color: var(--text-muted);">
                <hr style="flex: 1; border: 0; border-top: 1px solid var(--border);">
                <span style="font-size: 0.8rem; text-transform: uppercase;">o</span>
                <hr style="flex: 1; border: 0; border-top: 1px solid var(--border);">
            </div>

            <a href="{{ route('auth.google') }}" class="btn btn-secondary"
                style="width: 100%; justify-content: center; padding: 0.75rem; display: flex; align-items: center; gap: 0.75rem; text-decoration: none; border-radius: 0.75rem;">
                <svg width="20" height="20" viewBox="0 0 24 24">
                    <path fill="#4285F4"
                        d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" />
                    <path fill="#34A853"
                        d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" />
                    <path fill="#FBBC05"
                        d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z" />
                    <path fill="#EA4335"
                        d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" />
                </svg>
                Inicia sesión con Google
            </a>
        </div>
    </div>
@endsection
@extends('layouts.app')

@section('title', 'Recuperar Contraseña - Gestor de Usuarios')

@section('content')
    <div class="login-page">
        <div class="card login-card">
            <div style="text-align: center; margin-bottom: 2rem;">
                <h1 style="font-size: 2.5rem; color: var(--primary);">GR<span style="color: #fff"> Intranet</span><span style="color: var(--primary)"> EDU</span></h1>
                <p style="color: var(--text-muted);">Recuperar contraseña</p>
            </div>

            <p style="color: var(--text-muted); margin-bottom: 1.5rem; font-size: 0.95rem; line-height: 1.5;">
                ¿Olvidaste tu contraseña? No hay problema. Simplemente haznos saber tu dirección de correo electrónico y te
                enviaremos un enlace para restablecer la contraseña que te permitirá elegir una nueva.
            </p>

            @if ($errors->any())
                <div class="alert alert-error">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (session('status'))
                <div class="alert alert-success"
                    style="background: rgba(16, 185, 129, 0.1); color: #10b981; padding: 1rem; border-radius: 0.5rem; border: 1px solid rgba(16, 185, 129, 0.2); margin-bottom: 1rem;">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}">
                @csrf

                <div class="form-group">
                    <label for="email">Correo Electrónico</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                        placeholder="tu@email.com">
                </div>

                <div style="display: flex; flex-direction: column; gap: 1rem; margin-top: 1.5rem;">
                    <button type="submit" class="btn btn-primary"
                        style="width: 100%; justify-content: center; padding: 1rem;">
                        Enviar enlace de recuperación
                    </button>

                    <a href="{{ route('login') }}"
                        style="text-align: center; color: var(--text-muted); text-decoration: none; font-size: 0.9rem; transition: color 0.2s;"
                        onmouseover="this.style.color='var(--text)'" onmouseout="this.style.color='var(--text-muted)'">
                        Volver al inicio de sesión
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection
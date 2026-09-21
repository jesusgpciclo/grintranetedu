@extends('layouts.app')

@section('title', 'Restablecer Contraseña - Gestor de Usuarios')

@section('content')
    <div class="login-page">
        <div class="card login-card">
            <div style="text-align: center; margin-bottom: 2rem;">
                <h1 style="font-size: 2.5rem; color: var(--primary);">GR<span style="color: #fff"> Intranet</span><span style="color: var(--primary)"> EDU</span></h1>
                <p style="color: var(--text-muted);">Restablecer contraseña</p>
            </div>

            @if ($errors->any())
                <div class="alert alert-error">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('password.update') }}">
                @csrf

                <input type="hidden" name="token" value="{{ $token }}">

                <div class="form-group">
                    <label for="email">Correo Electrónico</label>
                    <input type="email" id="email" name="email" value="{{ $email ?? old('email') }}" required autofocus
                        readonly>
                </div>

                <div class="form-group">
                    <label for="password">Nueva Contraseña</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <div class="form-group">
                    <label for="password_confirmation">Confirmar Nueva Contraseña</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required>
                </div>

                <div style="margin-top: 1.5rem;">
                    <button type="submit" class="btn btn-primary"
                        style="width: 100%; justify-content: center; padding: 1rem;">
                        Restablecer contraseña
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
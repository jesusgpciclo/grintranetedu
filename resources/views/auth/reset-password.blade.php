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
                    <div class="password-wrapper">
                        <input type="password" id="password" name="password" required>
                        <button type="button" 
                                onclick="togglePasswordVisibility('password', this)" 
                                class="password-toggle-btn" 
                                title="Ver contraseña" 
                                aria-label="Ver contraseña">
                            <svg class="eye-show-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <svg class="eye-hide-icon" style="display: none;" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password_confirmation">Confirmar Nueva Contraseña</label>
                    <div class="password-wrapper">
                        <input type="password" id="password_confirmation" name="password_confirmation" required>
                        <button type="button" 
                                onclick="togglePasswordVisibility('password_confirmation', this)" 
                                class="password-toggle-btn" 
                                title="Ver contraseña" 
                                aria-label="Ver contraseña">
                            <svg class="eye-show-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <svg class="eye-hide-icon" style="display: none;" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                            </svg>
                        </button>
                    </div>
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
@extends('layouts.app')

@section('title', 'Mi Perfil - ' . $user->name)

@section('content')
<style>
    .avatar-option-btn {
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        cursor: pointer;
        border: 2px solid transparent;
        border-radius: 9999px;
        padding: 4px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .avatar-option-btn:hover {
        transform: scale(1.1);
        border-color: var(--primary);
    }

    .avatar-option-btn.is-selected {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px var(--primary-light);
        transform: scale(1.08);
    }

    .profile-card {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: 1.5rem;
        box-shadow: var(--shadow-sm);
        padding: 2rem;
        transition: background-color 0.2s ease, border-color 0.2s ease;
    }

    .form-label {
        display: block;
        font-size: 0.8rem;
        font-weight: 700;
        text-transform: uppercase;
        color: var(--text-muted);
        margin-bottom: 0.4rem;
        letter-spacing: 0.025em;
    }

    .form-input {
        width: 100%;
        padding: 0.75rem 1rem;
        border-radius: 0.75rem;
        background: var(--bg-input);
        border: 1px solid var(--border);
        color: var(--text-heading);
        font-size: 0.925rem;
        outline: none;
        transition: all 0.2s ease;
    }

    .form-input:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px var(--primary-light);
        background: var(--bg-input-focus);
    }

    .form-input:disabled, .form-input[readonly] {
        opacity: 0.6;
        cursor: not-allowed;
    }
</style>

<div class="profile-container max-w-4xl mx-auto space-y-6 pb-12">
    
    <!-- Must Change Password Urgent Alert -->
    @if($user->must_change_password)
        <div class="p-5 rounded-2xl bg-amber-500/15 border-2 border-amber-500/30 flex items-start gap-4 text-amber-900 dark:text-amber-200 shadow-sm animate-pulse">
            <span class="text-3xl">⚠️</span>
            <div>
                <h3 class="text-base font-extrabold tracking-tight">Cambio de contraseña requerido</h3>
                <p class="text-xs sm:text-sm mt-0.5 opacity-90">
                    Has iniciado sesión con una clave inicial o temporal. Por seguridad de tu cuenta y del centro educativo, debes establecer una nueva contraseña a continuación antes de continuar navegando.
                </p>
            </div>
        </div>
    @endif

    <!-- Header Banner -->
    <div class="bg-white dark:bg-slate-900/90 backdrop-blur-xl border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-sm dark:shadow-2xl transition-colors">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-sky-500 to-indigo-600 flex items-center justify-center text-white shadow-lg shadow-sky-500/25 shrink-0 overflow-hidden">
                    @if($user->avatar_url)
                        <img id="banner-avatar-preview" src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="w-full h-full object-cover">
                    @else
                        <span id="banner-avatar-initial" class="text-2xl font-black">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                    @endif
                </div>
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">Mi Perfil</h1>
                    <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
                        {{ $user->email }} • 
                        <span class="font-bold text-sky-600 dark:text-sky-400">{{ $user->departamento ?? 'Sin departamento' }}</span>
                    </p>
                </div>
            </div>

            <div>
                <a href="{{ route('dashboard') }}" 
                   class="inline-flex items-center gap-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-white font-semibold text-sm px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    <span>Volver al Panel</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Feedback Alerts -->
    @if (session('status'))
        <div class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 dark:text-emerald-400 font-bold text-sm flex items-center gap-3">
            <span class="text-lg">✓</span>
            <span>{{ session('status') }}</span>
        </div>
    @endif

    @if ($errors->any())
        <div class="p-4 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-600 dark:text-rose-400 font-medium text-sm">
            <div class="font-bold mb-1">Por favor corrige los siguientes errores:</div>
            <ul class="list-disc pl-5 space-y-0.5 text-xs">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Profile Form -->
    <div class="profile-card">
        <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
            @csrf
            @method('PATCH')

            <!-- SECTION 1: Avatar & Ilustraciones Predefinidas -->
            @if(!$isAlumno)
                <div class="p-6 rounded-2xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-800 space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div>
                            <h3 class="text-base font-extrabold text-slate-900 dark:text-white">Foto o Avatar de Perfil</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                Selecciona una de las ilustraciones para identificarte o sube tu propia foto.
                            </p>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="w-14 h-14 rounded-2xl overflow-hidden border-2 border-sky-500 shadow-md shrink-0 bg-white dark:bg-slate-900 flex items-center justify-center">
                                @if($user->avatar_url)
                                    <img id="current-avatar-img" src="{{ $user->avatar_url }}" alt="Avatar" class="w-full h-full object-cover">
                                @else
                                    <div id="current-avatar-letter" class="w-full h-full bg-sky-500 text-white flex items-center justify-center font-bold text-xl">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Predefined Avatars Gallery -->
                    <div>
                        <div class="form-label mb-2">Avatares Predefinidos (Pág. 4 del manual):</div>
                        <input type="hidden" name="predefined_avatar" id="predefined_avatar_input" value="">
                        
                        <div class="flex flex-wrap items-center gap-3">
                            @foreach($predefinedAvatars as $avatarPath)
                                @php
                                    $isSelected = ($user->avatar === $avatarPath);
                                @endphp
                                <button type="button" 
                                        onclick="selectPredefinedAvatar('{{ $avatarPath }}', this)"
                                        class="avatar-option-btn {{ $isSelected ? 'is-selected' : '' }}"
                                        title="Elegir este avatar">
                                    <img src="{{ asset($avatarPath) }}" alt="Avatar docente" class="w-12 h-12 rounded-full object-cover shadow-sm">
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <!-- Custom Photo Upload -->
                    <div class="pt-3 border-t border-slate-200 dark:border-slate-800/80">
                        <label for="avatar" class="form-label">O sube un archivo de imagen personalizado:</label>
                        <input type="file" id="avatar" name="avatar" accept="image/*" onchange="previewUploadedAvatar(this)"
                               class="text-xs text-slate-500 dark:text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-sky-50 dark:file:bg-sky-950/40 file:text-sky-600 dark:file:text-sky-400 hover:file:bg-sky-100 cursor-pointer">
                        <span class="text-[11px] text-slate-400 block mt-1">Formatos soportados: PNG, JPG, GIF (Máx. 2MB).</span>
                    </div>
                </div>
            @endif

            <!-- SECTION 2: Datos Personales & Departamento -->
            <div class="space-y-4">
                <h3 class="text-base font-extrabold text-slate-900 dark:text-white border-b border-slate-200 dark:border-slate-800 pb-2">
                    Datos del Profesor / Usuario
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="name" class="form-label">Nombre <span class="text-rose-500">*</span></label>
                        <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required
                               class="form-input" {{ $isAlumno ? 'readonly disabled' : '' }}>
                    </div>

                    <div>
                        <label for="last_name" class="form-label">Apellidos</label>
                        <input type="text" id="last_name" name="last_name" value="{{ old('last_name', $user->last_name) }}"
                               class="form-input" {{ $isAlumno ? 'readonly disabled' : '' }}>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="email" class="form-label">Correo Electrónico <span class="text-rose-500">*</span></label>
                        <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required
                               class="form-input" {{ $isAlumno ? 'readonly disabled' : '' }}>
                    </div>

                    @if(!$isAlumno)
                        <div>
                            <label for="departamento" class="form-label">Departamento Docente</label>
                            <input list="departamentos_list" id="departamento" name="departamento" 
                                   value="{{ old('departamento', $user->departamento) }}"
                                   placeholder="Ej: Matemáticas, Lengua, Física..."
                                   class="form-input">
                            <datalist id="departamentos_list">
                                @foreach($departamentos as $dep)
                                    <option value="{{ $dep }}">
                                @endforeach
                            </datalist>
                            <span class="text-[11px] text-slate-400 mt-1 block">
                                Se mostrará en el Parte de Guardia y cuadrantes junto a tu nombre.
                            </span>
                        </div>
                    @endif
                </div>

                <!-- Vinculación de Sustituto a Titular (Visible para Directiva/Admin o si ya está asignado) -->
                @if(($user->hasRole('admin') || $user->hasRole('directiva') || $user->titular_user_id) && !$isAlumno)
                    <div class="p-4 rounded-2xl bg-indigo-50 dark:bg-indigo-950/20 border border-indigo-200 dark:border-indigo-800/40 mt-4">
                        <label for="titular_user_id" class="form-label text-indigo-700 dark:text-indigo-300">
                            Profesor Titular (Sustituciones - Pág. 7 del manual):
                        </label>
                        @if($user->hasRole('admin') || $user->hasRole('directiva'))
                            <select id="titular_user_id" name="titular_user_id" class="form-input">
                                <option value="">-- No es sustituto (Profesor Titular) --</option>
                                @foreach($titulares as $titular)
                                    <option value="{{ $titular->id }}" {{ old('titular_user_id', $user->titular_user_id) == $titular->id ? 'selected' : '' }}>
                                        Sustituye a: {{ $titular->name }} {{ $titular->last_name ?? '' }} ({{ $titular->departamento ?? 'Sin depto' }})
                                    </option>
                                @endforeach
                            </select>
                        @else
                            <div class="text-sm font-bold text-indigo-900 dark:text-indigo-200 mt-1">
                                @if($user->titular)
                                    🛡️ Sustituyendo actualmente a: <strong>{{ $user->titular->name }} {{ $user->titular->last_name ?? '' }}</strong> (Heredas su horario de guardia).
                                @else
                                    Profesor Titular.
                                @endif
                            </div>
                        @endif
                        <span class="text-[11px] text-indigo-600/80 dark:text-indigo-400/80 block mt-1">
                            Los profesores sustitutos heredan automáticamente el horario de guardia del titular asignado.
                        </span>
                    </div>
                @endif
            </div>

            <!-- SECTION 3: Cambio de Contraseña -->
            <div id="password-section" class="space-y-4 pt-4 border-t border-slate-200 dark:border-slate-800">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-extrabold text-slate-900 dark:text-white">Seguridad y Contraseña</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                            @if($user->must_change_password)
                                <strong class="text-amber-600 dark:text-amber-400">Es obligatorio cambiar tu clave inicial ahora.</strong>
                            @else
                                Deja los campos vacíos si no deseas modificar tu clave actual.
                            @endif
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="password" class="form-label">
                            Nueva Contraseña {{ $user->must_change_password ? '*' : '' }}
                        </label>
                        <input type="password" id="password" name="password" 
                               placeholder="Mínimo 8 caracteres"
                               class="form-input" {{ $user->must_change_password ? 'required' : '' }}>
                    </div>

                    <div>
                        <label for="password_confirmation" class="form-label">
                            Confirmar Nueva Contraseña {{ $user->must_change_password ? '*' : '' }}
                        </label>
                        <input type="password" id="password_confirmation" name="password_confirmation" 
                               placeholder="Repite la nueva contraseña"
                               class="form-input" {{ $user->must_change_password ? 'required' : '' }}>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="pt-6 border-t border-slate-200 dark:border-slate-800 flex justify-end">
                <button type="submit" 
                        class="px-6 py-3 rounded-xl font-extrabold text-sm text-white shadow-lg shadow-sky-500/25 hover:shadow-sky-500/40 transition-all transform hover:-translate-y-0.5"
                        style="background: linear-gradient(135deg, var(--primary), #2563eb);">
                    Guardar Cambios del Perfil
                </button>
            </div>
        </form>
    </div>

</div>

<script>
    function selectPredefinedAvatar(avatarPath, btn) {
        // Set hidden input value
        document.getElementById('predefined_avatar_input').value = avatarPath;

        // Clear custom file input if any was selected
        const fileInput = document.getElementById('avatar');
        if (fileInput) fileInput.value = '';

        // Update selected state on buttons
        document.querySelectorAll('.avatar-option-btn').forEach(b => b.classList.remove('is-selected'));
        btn.classList.add('is-selected');

        // Update preview image
        const img = document.getElementById('current-avatar-img');
        const letter = document.getElementById('current-avatar-letter');
        const bannerImg = document.getElementById('banner-avatar-preview');
        const bannerLetter = document.getElementById('banner-avatar-initial');

        const fullUrl = '{{ asset('') }}' + avatarPath;

        if (img) {
            img.src = fullUrl;
        } else if (letter) {
            letter.outerHTML = '<img id="current-avatar-img" src="' + fullUrl + '" alt="Avatar" class="w-full h-full object-cover">';
        }

        if (bannerImg) {
            bannerImg.src = fullUrl;
        } else if (bannerLetter) {
            bannerLetter.outerHTML = '<img id="banner-avatar-preview" src="' + fullUrl + '" alt="Avatar" class="w-full h-full object-cover">';
        }
    }

    function previewUploadedAvatar(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                // Clear predefined avatar selection
                document.getElementById('predefined_avatar_input').value = '';
                document.querySelectorAll('.avatar-option-btn').forEach(b => b.classList.remove('is-selected'));

                const img = document.getElementById('current-avatar-img');
                const letter = document.getElementById('current-avatar-letter');
                const bannerImg = document.getElementById('banner-avatar-preview');
                const bannerLetter = document.getElementById('banner-avatar-initial');

                if (img) {
                    img.src = e.target.result;
                } else if (letter) {
                    letter.outerHTML = '<img id="current-avatar-img" src="' + e.target.result + '" alt="Avatar" class="w-full h-full object-cover">';
                }

                if (bannerImg) {
                    bannerImg.src = e.target.result;
                } else if (bannerLetter) {
                    bannerLetter.outerHTML = '<img id="banner-avatar-preview" src="' + e.target.result + '" alt="Avatar" class="w-full h-full object-cover">';
                }
            };
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
@endsection
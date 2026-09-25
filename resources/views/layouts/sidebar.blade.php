@php
    /** @var \App\Models\User $user */
    $user = Auth::user();
    $todayStr = now()->format('Y-m-d');
@endphp

<!-- Sidebar Backdrop for Mobile -->
<div id="sidebar-backdrop" class="sidebar-backdrop" aria-hidden="true"></div>

<aside id="main-sidebar" class="sidebar" aria-label="Menú principal de navegación">
    
    <!-- Sidebar Header / Branding -->
    <div class="sidebar-title flex items-center justify-between gap-2">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 text-decoration-none group">
            <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-sky-500 to-indigo-600 flex items-center justify-center text-white shadow-md shadow-sky-500/25 shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                </svg>
            </div>
            <div class="leading-tight">
                <span class="text-sm font-black text-slate-900 dark:text-white tracking-tight">GR Intranet EDU</span>
            </div>
        </a>
        <!-- Mobile Close Button (Touch-friendly >= 44px) -->
        <button id="sidebar-close-btn" onclick="closeMobileSidebar()" class="sidebar-close-btn min-w-[44px] min-h-[44px] flex items-center justify-center rounded-xl lg:hidden text-slate-400 hover:text-slate-700 dark:hover:text-white transition" aria-label="Cerrar menú">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                <path d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>

        <!-- Desktop Collapse Button (PC / Tablet >= 1024px) -->
        <button type="button" onclick="toggleDesktopSidebar()" id="sidebar-collapse-desktop-btn" 
                class="hidden lg:flex min-w-[36px] min-h-[36px] items-center justify-center rounded-xl text-slate-400 hover:text-slate-700 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 transition" 
                title="Ocultar menú lateral (Ctrl+B)" aria-label="Ocultar menú lateral">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
            </svg>
        </button>
    </div>

    <!-- User Profile & Theme Quick Switch -->
    <div class="user-sidebar-info"
        style="display: flex; align-items: center; gap: 0.85rem; margin: 0.75rem 0 1rem 0; padding: 0.85rem; background: var(--bg-hover); border: 1px solid var(--border); border-radius: 0.85rem;">
        <a href="{{ route('profile.edit') }}" class="flex items-center gap-2.5 flex-1 min-w-0 text-decoration-none group" title="Ver mi perfil">
            @if($user->avatar_url)
                <img src="{{ $user->avatar_url }}" alt="Avatar"
                    style="width: 38px; height: 38px; border-radius: 50%; object-fit: cover;" class="group-hover:scale-105 transition-transform duration-200">
            @else
                <div
                    style="width: 38px; height: 38px; border-radius: 50%; background: linear-gradient(135deg, var(--primary), #2563eb); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.95rem; box-shadow: var(--shadow-sm);" class="group-hover:scale-105 transition-transform duration-200">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
            @endif
            <div style="overflow: hidden; flex: 1;">
                <div
                    style="font-weight: 700; font-size: 0.875rem; color: var(--text-heading); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" class="group-hover:text-sky-500 transition-colors">
                    {{ $user->name }}
                </div>
                <div style="font-size: 0.725rem; color: var(--text-muted);">
                    {{ $user->getRoleNames()->map(fn($r) => ucfirst($r))->join(', ') ?: 'Docente' }}
                </div>
            </div>
        </a>
        <!-- Theme Toggle in Sidebar (Thumb-friendly >= 40px) -->
        <button type="button" onclick="toggleTheme()" class="theme-toggle-btn min-w-[40px] min-h-[40px] flex items-center justify-center" style="padding: 0.4rem; border-radius: 0.5rem;" title="Cambiar modo claro / oscuro">
            <svg class="theme-sun-icon w-4 h-4 text-amber-400 hidden" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                <path d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
            <svg class="theme-moon-icon w-4 h-4 text-sky-400 hidden" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                <path d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
            </svg>
        </button>
    </div>

    <!-- School Year Selector -->
    @if(isset($allSchoolYears) && count($allSchoolYears) > 0)
    <div class="school-year-selector" style="margin: 0 0 1rem 0; padding: 0.75rem; background: var(--bg-hover); border: 1px solid var(--border); border-radius: 0.75rem;">
        <form action="" method="GET" id="school-year-select-form">
            <label for="set_school_year_id" style="display: flex; align-items: center; gap: 0.4rem; font-size: 0.725rem; color: var(--text-muted); margin-bottom: 0.35rem; font-weight: 700;">
                <svg class="w-3.5 h-3.5 text-sky-500" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                    <path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                CURSO ACTIVO
            </label>
            <select name="set_school_year_id" id="set_school_year_id" onchange="this.form.submit()" style="width: 100%; background: var(--bg-input); color: var(--text-color); border: 1px solid var(--border); border-radius: 0.5rem; padding: 0.4rem 0.5rem; font-size: 0.825rem; font-family: var(--font-main); outline: none;">
                @foreach($allSchoolYears as $sy)
                    <option value="{{ $sy->id }}" {{ $sy->id == ($activeSchoolYearId ?? null) ? 'selected' : '' }}>
                        {{ $sy->name }}
                    </option>
                @endforeach
            </select>
        </form>
    </div>
    @endif

    <!-- Scrollable Navigation Area -->
    <div class="sidebar-scroll-area">
        <ul class="nav-links space-y-1">
        
        {{-- ═══ Dashboard ═══ --}}
        <li>
            <a href="{{ route('dashboard') }}"
                class="nav-link min-h-[44px] {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                    <path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                <span>Dashboard</span>
            </a>
        </li>

        {{-- ═══════════════════════════════════════════════════════════════ --}}
        {{-- 1. SECCIÓN: MI DÍA A DÍA (Para todo el claustro docente)       --}}
        {{-- ═══════════════════════════════════════════════════════════════ --}}
        @canany(['guardias.view', 'guardias.sign', 'ausencias.view', 'ausencias.create'])
        <li style="margin-top: 0.75rem; margin-bottom: 0.25rem;">
            <div style="padding: 0.35rem 0.75rem 0.25rem; display: flex; align-items: center; justify-content: space-between;">
                <span style="font-size: 0.68rem; font-weight: 900; text-transform: uppercase; letter-spacing: 0.05em; color: var(--primary);">
                    ☀️ Mi Día a Día
                </span>
                <span style="font-size: 0.6rem; font-weight: 800; text-transform: uppercase; padding: 0.1rem 0.4rem; border-radius: 4px; background: var(--primary-light); color: var(--primary);">
                    Guardias
                </span>
            </div>
        </li>

        <!-- 1.1 Parte de Guardia (En vivo) -->
        @can('guardias.view')
        <li>
            <a href="{{ route('guardias.parte') }}"
                class="nav-link min-h-[44px] {{ request()->routeIs('guardias.parte*') ? 'active' : '' }}"
                style="display: flex; align-items: center; justify-content: space-between;">
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <svg class="nav-icon text-sky-400" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                        <line x1="12" y1="8" x2="12" y2="12" />
                        <line x1="12" y1="16" x2="12.01" y2="16" />
                    </svg>
                    <span>Parte de Guardia</span>
                </div>
                <div>
                    @if(!empty($sidebarBadgeGuardias) && $sidebarBadgeGuardias > 0)
                        <span style="background: var(--danger-light); color: var(--danger); border: 1px solid var(--danger-border); font-size: 0.68rem; font-weight: 800; padding: 0.15rem 0.45rem; border-radius: 9999px; line-height: 1;">
                            {{ $sidebarBadgeGuardias }}
                        </span>
                    @else
                        <span style="background: var(--success-light); color: var(--success); font-size: 0.62rem; font-weight: 800; padding: 0.15rem 0.45rem; border-radius: 9999px; text-transform: uppercase;">
                            En vivo
                        </span>
                    @endif
                </div>
            </a>
        </li>
        @endcan

        <!-- 1.2 Mis Guardias Asignadas (con badge numérico si tiene guardia hoy) -->
        @canany(['guardias.sign', 'guardias.view'])
        <li>
            <a href="{{ route('guardias.asignadas') }}"
                class="nav-link min-h-[44px] {{ request()->routeIs('guardias.asignadas*') ? 'active' : '' }}"
                style="display: flex; align-items: center; justify-content: space-between;">
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <svg class="nav-icon text-emerald-400" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                        <circle cx="8.5" cy="7" r="4" />
                        <polyline points="17 11 19 13 23 9" />
                    </svg>
                    <span>Mis Guardias Asignadas</span>
                </div>
                <div>
                    @if(!empty($misGuardiasHoyCount) && $misGuardiasHoyCount > 0)
                        <span style="background: #f59e0b; color: #ffffff; font-size: 0.72rem; font-weight: 900; padding: 0.15rem 0.5rem; border-radius: 9999px; box-shadow: 0 1px 3px rgba(245, 158, 11, 0.4);" title="Tienes {{ $misGuardiasHoyCount }} guardia(s) asignada(s) hoy">
                            ⚡ {{ $misGuardiasHoyCount }}
                        </span>
                    @else
                        <span style="color: var(--text-muted); font-size: 0.7rem; font-weight: 700;">0</span>
                    @endif
                </div>
            </a>
        </li>
        @endcanany

        <!-- 1.3 Mis Ausencias (Comunicar falta / Dejar tareas) -->
        @canany(['ausencias.view', 'ausencias.create'])
        <li>
            <a href="{{ route('ausencias.index') }}"
                class="nav-link min-h-[44px] {{ request()->routeIs('ausencias.*') ? 'active' : '' }}">
                <svg class="nav-icon text-amber-400" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                    <line x1="16" y1="2" x2="16" y2="6" />
                    <line x1="8" y1="2" x2="8" y2="6" />
                    <line x1="3" y1="10" x2="21" y2="10" />
                    <line x1="10" y1="14" x2="14" y2="18" />
                    <line x1="14" y1="14" x2="10" y2="18" />
                </svg>
                <span>Mis Ausencias</span>
            </a>
        </li>
        @endcanany

        <!-- 1.4 Mi Horario de Guardias -->
        @can('guardias.view')
        <li>
            <a href="{{ route('guardias.mis-horas') }}"
                class="nav-link min-h-[44px] {{ request()->routeIs('guardias.mis-horas*') ? 'active' : '' }}">
                <svg class="nav-icon text-indigo-400" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10" />
                    <polyline points="12 6 12 12 16 14" />
                </svg>
                <span>Mi Horario de Guardias</span>
            </a>
        </li>
        @endcan
        @endcanany

        {{-- ═══════════════════════════════════════════════════════════════════ --}}
        {{-- 2. SECCIÓN: GESTIÓN Y DIRECCIÓN (Para directivo / admin)          --}}
        {{-- ═══════════════════════════════════════════════════════════════════ --}}
        @canany(['guardias.assign', 'guardias.manage', 'ausencias.justify'])
        <li style="margin-top: 1rem; margin-bottom: 0.25rem;">
            <div style="padding: 0.35rem 0.75rem 0.25rem; display: flex; align-items: center; justify-content: space-between;">
                <span style="font-size: 0.68rem; font-weight: 900; text-transform: uppercase; letter-spacing: 0.05em; color: #a855f7;">
                    🏛️ Gestión y Dirección
                </span>
                <span style="font-size: 0.6rem; font-weight: 800; text-transform: uppercase; padding: 0.1rem 0.4rem; border-radius: 4px; background: rgba(168, 85, 247, 0.15); color: #a855f7;">
                    Directiva
                </span>
            </div>
        </li>

        <!-- 2.1 Cuadrante Semanal Completo -->
        @canany(['guardias.assign', 'guardias.manage'])
        <li>
            <a href="{{ route('guardias.cuadrante') }}"
                class="nav-link min-h-[44px] {{ request()->routeIs('guardias.cuadrante*') ? 'active' : '' }}">
                <svg class="nav-icon text-purple-400" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                    <rect x="3" y="3" width="18" height="18" rx="2" />
                    <path d="M3 9h18" />
                    <path d="M3 15h18" />
                    <path d="M9 3v18" />
                    <path d="M15 3v18" />
                </svg>
                <span>Cuadrante Semanal Completo</span>
            </a>
        </li>
        @endcanany

        <!-- 2.2 Control de Justificaciones -->
        @can('ausencias.justify')
        <li>
            <a href="{{ route('guardias.justificaciones') }}"
                class="nav-link min-h-[44px] {{ request()->routeIs('guardias.justificaciones*') ? 'active' : '' }}">
                <svg class="nav-icon text-purple-400" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                    <polyline points="14 2 14 8 20 8" />
                    <path d="m9 15 2 2 4-4" />
                </svg>
                <span>Control de Justificaciones</span>
            </a>
        </li>
        @endcan

        <!-- 2.3 Estadísticas y Equidad del Claustro -->
        @can('guardias.manage')
        <li>
            <a href="{{ route('guardias.estadisticas') }}"
                class="nav-link min-h-[44px] {{ request()->routeIs('guardias.estadisticas*') ? 'active' : '' }}">
                <svg class="nav-icon text-purple-400" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                    <line x1="12" y1="20" x2="12" y2="10" />
                    <line x1="18" y1="20" x2="18" y2="4" />
                    <line x1="6" y1="20" x2="6" y2="16" />
                </svg>
                <span>Estadísticas y Equidad</span>
            </a>
        </li>

        <!-- 2.4 Configuración de Tramos y Aulas -->
        <li>
            <a href="{{ route('guardias.configuracion') }}"
                class="nav-link min-h-[44px] {{ (request()->routeIs('guardias.configuracion*') || request()->routeIs('aulas.*')) ? 'active' : '' }}">
                <svg class="nav-icon text-purple-400" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                    <line x1="4" y1="21" x2="4" y2="14" />
                    <line x1="4" y1="10" x2="4" y2="3" />
                    <line x1="12" y1="21" x2="12" y2="12" />
                    <line x1="12" y1="8" x2="12" y2="3" />
                    <line x1="20" y1="21" x2="20" y2="16" />
                    <line x1="20" y1="12" x2="20" y2="3" />
                    <line x1="1" y1="14" x2="7" y2="14" />
                    <line x1="9" y1="8" x2="15" y2="8" />
                    <line x1="17" y1="16" x2="23" y2="16" />
                </svg>
                <span>Configuración de Tramos y Aulas</span>
            </a>
        </li>
        @endcan
        @endcanany

        {{-- ═══════════════════════════════════════════════════════════════════ --}}
        {{-- 3. MÓDULOS DE GESTIÓN EDUCATIVA Y CENTRO (Resto de la aplicación) --}}
        {{-- ═══════════════════════════════════════════════════════════════════ --}}
        <li style="margin-top: 1rem; margin-bottom: 0.25rem;">
            <div style="padding: 0.35rem 0.75rem 0.25rem;">
                <span style="font-size: 0.68rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-muted);">
                    🏢 Módulos del Centro
                </span>
            </div>
        </li>

        {{-- ═══ Mi Espacio (Personal) ═══ --}}
        <li class="nav-item-has-submenu">
            <div class="nav-link submenu-trigger min-h-[44px]">
                <div class="trigger-content">
                    <svg class="nav-icon text-sky-400" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                        <path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    <span>Mi Espacio</span>
                </div>
                <span class="arrow">▼</span>
            </div>
            <ul class="submenu">
                <li>
                    <a href="{{ route('profile.edit') }}"
                        class="nav-link min-h-[44px] {{ request()->routeIs('profile.*') ? 'active' : '' }}">
                        <svg class="nav-icon" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <path d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>Mi Perfil</span>
                    </a>
                </li>
                @can('schedules.view')
                <li>
                    <a href="{{ route('personal-schedules.index') }}"
                        class="nav-link min-h-[44px] {{ request()->routeIs('personal-schedules.*') ? 'active' : '' }}">
                        <svg class="nav-icon" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <span>Mi Horario Personal</span>
                    </a>
                </li>
                @endcan
                @can('calendars.view')
                <li>
                    <a href="{{ route('calendar.index') }}"
                        class="nav-link min-h-[44px] {{ request()->routeIs('calendar.*') ? 'active' : '' }}">
                        <svg class="nav-icon" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <span>Calendario Escolar</span>
                    </a>
                </li>
                @endcan
            </ul>
        </li>

        {{-- ═══ Mensajería Interna ═══ --}}
        @can('messages.view')
        <li>
            <a href="{{ route('messages.index') }}"
                class="nav-link min-h-[44px] {{ request()->routeIs('messages.*') ? 'active' : '' }}">
                <svg class="nav-icon text-indigo-400" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                    <path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                <span>Mensajería</span>
            </a>
        </li>
        @endcan

        {{-- ═══ Centro (Horarios, Aulas, Grupos, Profesores, Alumnos) ═══ --}}
        @canany(['school_years.manage', 'calendars.manage', 'calendars.view', 'schedules.manage', 'aulas.view', 'aulas.manage', 'zonas.view', 'zonas.manage', 'groups.view', 'teachers.view', 'students.view'])
        <li class="nav-item-has-submenu">
            <div class="nav-link submenu-trigger min-h-[44px]">
                <div class="trigger-content">
                    <svg class="nav-icon text-sky-400" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                        <path d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    <span>Centro</span>
                </div>
                <span class="arrow">▼</span>
            </div>
            <ul class="submenu">
                @can('school_years.manage')
                <li>
                    <a href="{{ route('school-years.index') }}"
                        class="nav-link min-h-[44px] {{ request()->routeIs('school-years.*') ? 'active' : '' }}">
                        <svg class="nav-icon" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <path d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                        <span>Cursos</span>
                    </a>
                </li>
                @endcan
                @canany(['calendars.manage', 'calendars.view'])
                <li>
                    <a href="{{ route('calendar.index') }}"
                        class="nav-link min-h-[44px] {{ request()->routeIs('calendar.*') ? 'active' : '' }}">
                        <svg class="nav-icon" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <span>Calendarios</span>
                    </a>
                </li>
                @endcanany
                @can('schedules.manage')
                <li>
                    <a href="{{ route('schedule-templates.index') }}"
                        class="nav-link min-h-[44px] {{ request()->routeIs('schedule-templates.*') ? 'active' : '' }}">
                        <svg class="nav-icon" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <path d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" />
                        </svg>
                        <span>Plantillas Horarios</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('teacher-schedules.index') }}"
                        class="nav-link min-h-[44px] {{ request()->routeIs('teacher-schedules.*') ? 'active' : '' }}">
                        <svg class="nav-icon" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <span>Horarios</span>
                    </a>
                </li>
                @endcan
                @canany(['aulas.view', 'aulas.manage', 'zonas.view', 'zonas.manage'])
                <li>
                    <a href="{{ route('aulas.index') }}"
                        class="nav-link min-h-[44px] {{ request()->routeIs('aulas.*') ? 'active' : '' }}">
                        <svg class="nav-icon" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <path d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span>Zonas</span>
                    </a>
                </li>
                @endcanany
                @can('groups.view')
                <li>
                    <a href="{{ route('groups.index') }}"
                        class="nav-link min-h-[44px] {{ request()->routeIs('groups.*') ? 'active' : '' }}">
                        <svg class="nav-icon" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <span>Grupos</span>
                    </a>
                </li>
                @endcan
                @can('teachers.view')
                <li>
                    <a href="{{ route('teachers.index') }}"
                        class="nav-link min-h-[44px] {{ request()->routeIs('teachers.*') ? 'active' : '' }}">
                        <svg class="nav-icon" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <path d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        <span>Profesores</span>
                    </a>
                </li>
                @endcan
                @can('students.view')
                <li>
                    <a href="{{ route('students.index') }}"
                        class="nav-link min-h-[44px] {{ request()->routeIs('students.*') ? 'active' : '' }}">
                        <svg class="nav-icon" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <path d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222" />
                        </svg>
                        <span>Alumnos</span>
                    </a>
                </li>
                @endcan
            </ul>
        </li>
        @endcanany

        {{-- ═══ Salidas (Módulo de Salidas) ═══ --}}
        @canany(['salidas.view', 'salidas.create', 'salidas.return_monitor', 'salidas.manage'])
        <li class="nav-item-has-submenu">
            <div class="nav-link submenu-trigger min-h-[44px]">
                <div class="trigger-content">
                    <svg class="nav-icon text-amber-500" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                        <path d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    <span>Salidas</span>
                </div>
                <div style="display: flex; align-items: center; gap: 0.4rem;">
                    @if(!empty($sidebarBadgeSalidas) && $sidebarBadgeSalidas > 0)
                        <span style="background: var(--warning-light); color: var(--warning); border: 1px solid var(--warning-border); font-size: 0.68rem; font-weight: 700; padding: 0.1rem 0.4rem; border-radius: 9999px; line-height: 1;">
                            {{ $sidebarBadgeSalidas }}
                        </span>
                    @endif
                    <span class="arrow">▼</span>
                </div>
            </div>
            <ul class="submenu">
                @can('salidas.create')
                <li>
                    <a href="{{ route('salidas.index') }}"
                        class="nav-link min-h-[44px] {{ request()->routeIs('salidas.index') ? 'active' : '' }}">
                        <svg class="nav-icon" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <path d="M12 4v16m8-8H4" />
                        </svg>
                        <span>Pase de Salida</span>
                    </a>
                </li>
                @endcan
                @can('salidas.manage')
                <li>
                    <a href="{{ route('salidas.history') }}"
                        class="nav-link min-h-[44px] {{ request()->routeIs('salidas.history') ? 'active' : '' }}">
                        <svg class="nav-icon" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>Historial</span>
                    </a>
                </li>
                @endcan
                @canany(['salidas.view', 'salidas.return_monitor'])
                <li>
                    <a href="{{ route('salidas.monitor') }}"
                        class="nav-link min-h-[44px] {{ request()->routeIs('salidas.monitor') ? 'active' : '' }}" style="display: flex; justify-content: space-between; align-items: center;">
                        <div style="display: flex; align-items: center; gap: 0.6rem;">
                            <svg class="nav-icon" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                <path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <span>Monitor</span>
                        </div>
                        @if(!empty($sidebarBadgeSalidas) && $sidebarBadgeSalidas > 0)
                            <span style="background: var(--warning-light); color: var(--warning); border: 1px solid var(--warning-border); font-size: 0.68rem; font-weight: 700; padding: 0.1rem 0.4rem; border-radius: 9999px; line-height: 1;">
                                {{ $sidebarBadgeSalidas }}
                            </span>
                        @endif
                    </a>
                </li>
                @endcanany
            </ul>
        </li>
        @endcanany

        {{-- ═══ Cuaderno del Profesor ═══ --}}
        @canany(['cuaderno.view', 'cuaderno.manage', 'modulos.view', 'modulos.manage', 'notas.manage'])
        <li class="nav-item-has-submenu">
            <div class="nav-link submenu-trigger min-h-[44px]">
                <div class="trigger-content">
                    <svg class="nav-icon text-purple-500" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                        <path d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                    <span>Cuaderno del Profesor</span>
                </div>
                <span class="arrow">▼</span>
            </div>
            <ul class="submenu">
                @canany(['cuaderno.view', 'cuaderno.manage'])
                <li>
                    <a href="{{ route('cuaderno.index') }}"
                        class="nav-link min-h-[44px] {{ request()->routeIs('cuaderno.*') ? 'active' : '' }}">
                        <svg class="nav-icon" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span>Mi Cuaderno</span>
                    </a>
                </li>
                @endcanany
                @canany(['modulos.view', 'modulos.manage'])
                <li>
                    <a href="{{ route('modulos.index') }}"
                        class="nav-link min-h-[44px] {{ request()->routeIs('modulos.*') ? 'active' : '' }}">
                        <svg class="nav-icon" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <path d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                        <span>Módulos Formativos</span>
                    </a>
                </li>
                @endcanany
                @can('cuaderno.manage')
                <li>
                    <a href="{{ route('sesiones.index') }}"
                        class="nav-link min-h-[44px] {{ request()->routeIs('sesiones.*') ? 'active' : '' }}">
                        <svg class="nav-icon" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <span>Sesiones y Asistencia</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('actividades.index') }}"
                        class="nav-link min-h-[44px] {{ request()->routeIs('actividades.*') ? 'active' : '' }}">
                        <svg class="nav-icon" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                        </svg>
                        <span>Actividades</span>
                    </a>
                </li>
                @endcan
                @can('notas.manage')
                <li>
                    <a href="{{ route('notas.index') }}"
                        class="nav-link min-h-[44px] {{ request()->routeIs('notas.*') ? 'active' : '' }}">
                        <svg class="nav-icon" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <path d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" />
                            <path d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" />
                        </svg>
                        <span>Notas y Calificaciones</span>
                    </a>
                </li>
                @endcan
                @canany(['cuaderno.view', 'cuaderno.manage'])
                <li>
                    <a href="{{ route('observaciones.index') }}"
                        class="nav-link min-h-[44px] {{ request()->routeIs('observaciones.*') ? 'active' : '' }}">
                        <svg class="nav-icon" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <path d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                        </svg>
                        <span>Observaciones Alumnos</span>
                    </a>
                </li>
                @endcanany
            </ul>
        </li>
        @endcanany

        {{-- ═══ Recursos e Incidencias ═══ --}}
        @canany(['tic.view', 'tic.manage', 'incidencias.view', 'incidencias.manage'])
        <li class="nav-item-has-submenu">
            <div class="nav-link submenu-trigger min-h-[44px]">
                <div class="trigger-content">
                    <svg class="nav-icon text-emerald-500" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                        <path d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    <span>Recursos e Incidencias</span>
                </div>
                <div style="display: flex; align-items: center; gap: 0.4rem;">
                    @if(!empty($sidebarBadgeIncidencias) && $sidebarBadgeIncidencias > 0)
                        <span style="background: var(--primary-light); color: var(--primary); border: 1px solid var(--primary-border); font-size: 0.68rem; font-weight: 700; padding: 0.1rem 0.4rem; border-radius: 9999px; line-height: 1;">
                            {{ $sidebarBadgeIncidencias }}
                        </span>
                    @endif
                    <span class="arrow">▼</span>
                </div>
            </div>
            <ul class="submenu">
                @can('tic.view')
                <li>
                    <a href="{{ route('tic-bookings.index') }}"
                        class="nav-link min-h-[44px] {{ request()->routeIs('tic-bookings.*') ? 'active' : '' }}">
                        <svg class="nav-icon" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <path d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        <span>Reservas TIC</span>
                    </a>
                </li>
                @endcan
                @can('tic.manage')
                <li>
                    <a href="{{ route('recursos.index') }}"
                        class="nav-link min-h-[44px] {{ request()->routeIs('recursos.*') ? 'active' : '' }}">
                        <svg class="nav-icon" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                        <span>Recursos Físicos</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('tipo-recursos.index') }}"
                        class="nav-link min-h-[44px] {{ request()->routeIs('tipo-recursos.*') ? 'active' : '' }}">
                        <svg class="nav-icon" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <path d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                        </svg>
                        <span>Tipos de Recursos</span>
                    </a>
                </li>
                @endcan
                @canany(['incidencias.view', 'incidencias.manage'])
                <li>
                    <a href="{{ route('incidencias.index') }}"
                        class="nav-link min-h-[44px] {{ request()->routeIs('incidencias.*') ? 'active' : '' }}" style="display: flex; justify-content: space-between; align-items: center;">
                        <div style="display: flex; align-items: center; gap: 0.6rem;">
                            <svg class="nav-icon" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                <path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <span>Incidencias</span>
                        </div>
                        @if(!empty($sidebarBadgeIncidencias) && $sidebarBadgeIncidencias > 0)
                            <span style="background: var(--primary-light); color: var(--primary); border: 1px solid var(--primary-border); font-size: 0.68rem; font-weight: 700; padding: 0.1rem 0.4rem; border-radius: 9999px; line-height: 1;">
                                {{ $sidebarBadgeIncidencias }}
                            </span>
                        @endif
                    </a>
                </li>
                @endcanany
            </ul>
        </li>
        @endcanany

        {{-- ═══ Documentación ═══ --}}
        @canany(['documentos.view', 'documentos.manage', 'tic.manage', 'inventory.view', 'inventory.manage'])
        <li class="nav-item-has-submenu">
            <div class="nav-link submenu-trigger min-h-[44px]">
                <div class="trigger-content">
                    <svg class="nav-icon text-teal-500" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                        <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span>Documentación</span>
                </div>
                <span class="arrow">▼</span>
            </div>
            <ul class="submenu">
                @can('documentos.view')
                <li>
                    <a href="{{ route('documentos-institucionales.index') }}"
                        class="nav-link min-h-[44px] {{ request()->routeIs('documentos-institucionales.*') ? 'active' : '' }}">
                        <svg class="nav-icon" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <path d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z" />
                        </svg>
                        <span>Docs Institucionales</span>
                    </a>
                </li>
                @endcan
                @can('documentos.manage')
                <li>
                    <a href="{{ route('categorias.index') }}"
                        class="nav-link min-h-[44px] {{ request()->routeIs('categorias.*') ? 'active' : '' }}">
                        <svg class="nav-icon" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <path d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                        </svg>
                        <span>Categorías</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('etiquetas.index') }}"
                        class="nav-link min-h-[44px] {{ request()->routeIs('etiquetas.*') ? 'active' : '' }}">
                        <svg class="nav-icon" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <path d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                        </svg>
                        <span>Etiquetas</span>
                    </a>
                </li>
                @endcan
                @canany(['inventory.view', 'inventory.manage', 'tic.manage'])
                <li>
                    <a href="{{ route('inventory.index') }}"
                        class="nav-link min-h-[44px] {{ request()->routeIs('inventory.*') ? 'active' : '' }}">
                        <svg class="nav-icon" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                        </svg>
                        <span>Inventario Centro</span>
                    </a>
                </li>
                @endcanany
            </ul>
        </li>
        @endcanany

        {{-- ═══ Administración ═══ --}}
        @canany(['users.view', 'roles.view', 'permissions.manage', 'backups.manage', 'actualizaciones.manage'])
        <li class="nav-item-has-submenu">
            <div class="nav-link submenu-trigger min-h-[44px]">
                <div class="trigger-content">
                    <svg class="nav-icon text-rose-500" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                        <path d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span>Administración</span>
                </div>
                <span class="arrow">▼</span>
            </div>
            <ul class="submenu">
                @can('users.view')
                <li>
                    <a href="{{ route('users.index') }}"
                        class="nav-link min-h-[44px] {{ request()->routeIs('users.*') ? 'active' : '' }}">
                        <svg class="nav-icon" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <path d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        <span>Usuarios</span>
                    </a>
                </li>
                @endcan
                @can('roles.view')
                <li>
                    <a href="{{ route('roles.index') }}"
                        class="nav-link min-h-[44px] {{ request()->routeIs('roles.*') ? 'active' : '' }}">
                        <svg class="nav-icon" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                        <span>Roles</span>
                    </a>
                </li>
                @endcan
                @can('backups.manage')
                <li>
                    <a href="{{ route('backups.index') }}"
                        class="nav-link min-h-[44px] {{ request()->routeIs('backups.*') ? 'active' : '' }}">
                        <svg class="nav-icon" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <path d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4" />
                        </svg>
                        <span>Copias de Seguridad</span>
                    </a>
                </li>
                @endcan
                @can('actualizaciones.manage')
                <li>
                    <a href="{{ route('actualizaciones.index') }}"
                        class="nav-link min-h-[44px] {{ request()->routeIs('actualizaciones.*') ? 'active' : '' }}">
                        <svg class="nav-icon" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <path d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        <span>Actualizaciones</span>
                    </a>
                </li>
                @endcan
            </ul>
        </li>
        @endcanany

        {{-- ═══ Política de Privacidad ═══ --}}
        <li>
            <a href="{{ route('privacidad') }}" class="nav-link min-h-[44px] {{ request()->routeIs('privacidad') ? 'active' : '' }}" style="font-size: 0.8rem; color: var(--text-muted);">
                <svg class="nav-icon" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                    <path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
                <span>Privacidad (RGPD)</span>
            </a>
        </li>
    </ul>
    </div>

    <!-- Fixed Bottom Footer (Cerrar Sesión) -->
    <div class="sidebar-footer shrink-0 pt-2" style="border-top: 1px solid var(--border);">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="nav-link min-h-[44px]"
                style="width: 100%; text-align: left; background: var(--danger-light); border: 1px solid var(--danger-border); cursor: pointer; color: var(--danger); font-weight: 700;">
                <svg class="nav-icon text-rose-500" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                    <path d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
                <span>Cerrar Sesión</span>
            </button>
        </form>
    </div>
</aside>

@foreach($students as $student)
    @php
        $activePass = $activePasses->firstWhere('user_id', $student->id);
        $studentTodayPasses = $todayPassesByStudent->get($student->id, collect());
        $todayCount = $studentTodayPasses->count();
        $lastPass = $studentTodayPasses->first();
        $studentDisplayName = $student->last_name ? trim($student->last_name . ', ' . $student->name) : $student->name;
        $studentFullName = trim($student->name . ' ' . ($student->last_name ?? ''));
        $courseName = trim(($student->groupRel?->course ? $student->groupRel->course . ' ' : '') . ($student->groupRel?->name ?? ''));
    @endphp
    <div class="student-card group relative rounded-2xl p-2.5 sm:p-3 shadow-xs hover:shadow-md transition-all duration-200 border flex items-center justify-between gap-2.5 w-full"
        style="background: var(--bg-card); border-color: {{ $activePass ? 'rgba(245, 158, 11, 0.6)' : ($todayCount >= 3 ? 'rgba(239, 68, 68, 0.4)' : 'var(--border)') }};"
        data-group-id="{{ $student->group_id }}" data-id="{{ $student->id }}"
        data-student-name="{{ $studentDisplayName }}"
        data-student-course="{{ $courseName }}"
        data-today-count="{{ $todayCount }}"
        data-last-exit="{{ $lastPass?->start_time ? $lastPass->start_time->format('H:i') : '' }}"
        data-search="{{ strtolower(($student->last_name ?? '') . ' ' . $student->name . ' ' . $student->name . ' ' . ($student->last_name ?? '')) }}">

        <!-- Left: Avatar + Name + Course + Exits Today -->
        <div class="flex items-center gap-2.5 min-w-0 flex-1">
            <div class="student-avatar h-9 w-9 sm:h-10 sm:w-10 shrink-0 rounded-full bg-gradient-to-tr {{ $activePass ? 'from-amber-400 to-amber-600' : ($todayCount >= 3 ? 'from-amber-500 to-rose-500' : 'from-blue-500 to-indigo-600') }} flex items-center justify-center text-white font-black text-xs sm:text-sm shadow-xs">
                {{ $student->initials }}
            </div>
            <div class="overflow-hidden min-w-0 flex-1">
                <div class="student-name-container flex items-center gap-1.5">
                    <h3 class="student-name student-name-mobile text-base sm:text-lg font-bold text-[var(--text-heading)] truncate leading-tight"
                        title="{{ $studentDisplayName }}">
                        {{ $studentDisplayName }}
                    </h3>
                    @if($todayCount > 0 && !$activePass)
                        <span class="today-exits-badge text-[10px] font-extrabold px-1.5 py-0.5 rounded-md {{ $todayCount >= 3 ? 'bg-rose-500/15 text-rose-500 border border-rose-500/30' : 'bg-amber-500/15 text-amber-500 border border-amber-500/30' }}" title="{{ $todayCount }} salidas hoy">
                            {{ $todayCount }}
                        </span>
                    @endif
                </div>
                <div class="student-status-line student-course-mobile flex items-center gap-1 text-[11px] text-[var(--text-muted)] leading-tight mt-0.5">
                    <span class="truncate">{{ $courseName }}</span>
                    @if($activePass)
                        <span class="active-fuera-badge text-amber-500 font-extrabold flex items-center gap-1 shrink-0 ml-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-ping"></span>
                            <span>Fuera: {{ $activePass->reason }}</span>
                        </span>
                    @elseif($todayCount > 0)
                        <span class="last-exit-text text-[10px] text-[var(--text-muted)] truncate hidden xs:inline">• Última: {{ $lastPass?->start_time ? $lastPass->start_time->format('H:i') : '' }}</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right: Actions (Active state has Timer + Regresar; Inactive state has ONLY Servicio icon and More [+] icon) -->
        @if($activePass)
            <div class="card-actions flex items-center gap-1.5 shrink-0">
                <div class="px-2 py-1 bg-amber-500/15 border border-amber-500/30 rounded-xl text-center">
                    <span class="text-[11px] font-mono font-black text-amber-500 timer block leading-none" data-start="{{ $activePass->start_time->timestamp }}">00:00</span>
                </div>
                <button onclick="endPass({{ $activePass->id }})" class="px-2.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white rounded-xl font-bold text-xs flex items-center gap-1 shadow-xs transition-all">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    <span>Regresar</span>
                </button>
            </div>
        @else
            <div class="card-actions flex items-center gap-1 shrink-0">
                <!-- 1. Icono de servicio (Baño / Aseo / WC) -->
                <button type="button"
                    onclick="createPass({{ $student->id }}, 'Baño', {{ $todayCount }}, '{{ $lastPass?->start_time ? $lastPass->start_time->format('H:i') : '' }}')"
                    title="Salida a servicio / baño" aria-label="Baño"
                    class="flex items-center justify-center w-8 h-8 rounded-xl bg-blue-500/15 hover:bg-blue-500/25 active:scale-95 text-blue-600 dark:text-blue-400 border border-blue-500/30 transition shadow-xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m8-2a2 2 0 100-4 2 2 0 000 4zM7 8h10M7 12h10" />
                    </svg>
                </button>

                <!-- 2. Icono de más (+) -->
                <button type="button"
                    onclick="openStudentDetailsModal({{ $student->id }})"
                    title="Más opciones de salida y detalles" aria-label="Más opciones"
                    class="flex items-center justify-center w-8 h-8 rounded-xl bg-slate-500/15 hover:bg-slate-500/25 active:scale-95 text-slate-700 dark:text-slate-300 border border-slate-500/30 transition shadow-xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                    </svg>
                </button>
            </div>
        @endif
    </div>
@endforeach

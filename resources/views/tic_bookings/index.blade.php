@extends('layouts.app')

@section('title', 'Reservas de Recursos TIC')

@section('content')
<div class="page-header">
    <h1 class="page-title">Reservas de Recursos TIC</h1>
</div>

<div class="card calendar-controls" style="display: flex; flex-direction: column; gap: 1.5rem; margin-bottom: 2rem;">
    <form method="GET" action="{{ route('tic-bookings.index') }}" class="flex gap-4 items-end flex-wrap w-full">
        <input type="hidden" name="view" value="{{ $view }}">
        <input type="hidden" name="date" value="{{ $dateParam }}">
        <div class="form-group" style="margin-bottom: 0; min-width: 250px;">
            <label for="resource_id" class="text-sm text-gray-400">Seleccionar Recurso</label>
            <select name="resource_id" id="resource_id" class="form-control" onchange="this.form.submit()" style="background: #1f2937; border: 1px solid var(--border); border-radius: 0.5rem; padding: 0.5rem; color: white;">
                <option value="">-- Elige un recurso --</option>
                @foreach($categories as $category)
                    <optgroup label="{{ $category->name }}">
                        @foreach($category->resources as $resource)
                            <option value="{{ $resource->id }}" {{ ($selectedResource && $selectedResource->id == $resource->id) ? 'selected' : '' }}>
                                {{ $resource->name }}
                            </option>
                        @endforeach
                    </optgroup>
                @endforeach
            </select>
        </div>

        <div class="view-selector" style="display: flex; gap: 0.5rem;">
            <a href="{{ route('tic-bookings.index', ['resource_id' => request('resource_id'), 'view' => 'day', 'date' => $dateParam]) }}"
                class="btn {{ $view === 'day' ? 'btn-primary' : '' }}" style="{{ $view !== 'day' ? 'background: rgba(255,255,255,0.05); border: 1px solid var(--border); color: white;' : 'border: 1px solid var(--primary);' }}">Día</a>
            <a href="{{ route('tic-bookings.index', ['resource_id' => request('resource_id'), 'view' => 'week', 'date' => $dateParam]) }}"
                class="btn {{ $view === 'week' ? 'btn-primary' : '' }}" style="{{ $view !== 'week' ? 'background: rgba(255,255,255,0.05); border: 1px solid var(--border); color: white;' : 'border: 1px solid var(--primary);' }}">Semana</a>
            <a href="{{ route('tic-bookings.index', ['resource_id' => request('resource_id'), 'view' => 'month', 'date' => $dateParam]) }}"
                class="btn {{ $view === 'month' ? 'btn-primary' : '' }}" style="{{ $view !== 'month' ? 'background: rgba(255,255,255,0.05); border: 1px solid var(--border); color: white;' : 'border: 1px solid var(--primary);' }}">Mes</a>
            <a href="{{ route('tic-bookings.index', ['resource_id' => request('resource_id'), 'view' => 'year', 'date' => $dateParam]) }}"
                class="btn {{ $view === 'year' ? 'btn-primary' : '' }}" style="{{ $view !== 'year' ? 'background: rgba(255,255,255,0.05); border: 1px solid var(--border); color: white;' : 'border: 1px solid var(--primary);' }}">Año</a>
        </div>

        <div class="date-navigation" style="display: flex; gap: 0.5rem; margin-left: auto;">
            @php
                $carbonDate = \Carbon\Carbon::parse($dateParam);
                $prevDate = match($view) {
                    'day' => $carbonDate->copy()->subDay()->format('Y-m-d'),
                    'week' => $carbonDate->copy()->subWeek()->format('Y-m-d'),
                    'month' => $carbonDate->copy()->subMonth()->format('Y-m-d'),
                    'year' => $carbonDate->copy()->subYear()->format('Y-m-d'),
                };
                $nextDate = match($view) {
                    'day' => $carbonDate->copy()->addDay()->format('Y-m-d'),
                    'week' => $carbonDate->copy()->addWeek()->format('Y-m-d'),
                    'month' => $carbonDate->copy()->addMonth()->format('Y-m-d'),
                    'year' => $carbonDate->copy()->addYear()->format('Y-m-d'),
                };
            @endphp
            <a href="{{ route('tic-bookings.index', ['resource_id' => request('resource_id'), 'view' => $view, 'date' => $prevDate]) }}" class="btn btn-icon" style="background: rgba(255,255,255,0.05); border: 1px solid var(--border); padding: 0.5rem 1rem; border-radius: 0.5rem; color: white;">&laquo; Ant</a>
            <a href="{{ route('tic-bookings.index', ['resource_id' => request('resource_id'), 'view' => $view, 'date' => date('Y-m-d')]) }}" class="btn btn-icon" style="background: rgba(255,255,255,0.05); border: 1px solid var(--border); padding: 0.5rem 1rem; border-radius: 0.5rem; color: white;">Hoy</a>
            <a href="{{ route('tic-bookings.index', ['resource_id' => request('resource_id'), 'view' => $view, 'date' => $nextDate]) }}" class="btn btn-icon" style="background: rgba(255,255,255,0.05); border: 1px solid var(--border); padding: 0.5rem 1rem; border-radius: 0.5rem; color: white;">Sig &raquo;</a>
        </div>
    </form>
</div>

@if(session('success'))
    <div class="alert alert-success" style="background: rgba(34, 197, 94, 0.2); border: 1px solid #22c55e; color: #4ade80; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1rem;">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger" style="background: rgba(239, 68, 68, 0.2); border: 1px solid #ef4444; color: #f87171; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1rem;">
        {{ session('error') }}
    </div>
@endif

@if($selectedResource)
    <div class="card">
        <h3 style="margin-bottom: 1rem; color: var(--primary);">
            Horario de: {{ $selectedResource->name }}
            @if($view === 'day')
                - {{ \Carbon\Carbon::parse($dateParam)->translatedFormat('d \d\e F \d\e Y') }}
            @elseif($view === 'week')
                - Semana del {{ \Carbon\Carbon::parse($dateParam)->startOfWeek()->format('d/m') }}
            @elseif($view === 'month')
                - {{ \Carbon\Carbon::parse($dateParam)->translatedFormat('F Y') }}
            @elseif($view === 'year')
                @php
                    $yDate = \Carbon\Carbon::parse($dateParam);
                    $startYear = $yDate->month <= 6 ? $yDate->year - 1 : $yDate->year;
                @endphp
                - Curso {{ $startYear }}/{{ $startYear + 1 }}
            @endif
        </h3>
        
        @if($view === 'day' || $view === 'week')
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; text-align: left;">
                    <thead>
                        <tr>
                            <th style="padding: 1rem; border-bottom: 1px solid var(--border); background: rgba(255,255,255,0.05);">Tramo Horario</th>
                            @foreach($days as $day)
                                <th style="padding: 1rem; border-bottom: 1px solid var(--border); background: rgba(255,255,255,0.05); text-align: center; {{ $day['is_today'] ? 'border-top: 2px solid var(--primary);' : '' }} {{ $day['is_holiday'] ? 'border-top: 2px solid #ef4444;' : '' }}">
                                    <div style="font-weight: bold; {{ $day['is_holiday'] ? 'color: #ef4444;' : '' }}">{{ ucfirst($day['dayName']) }}</div>
                                    <div style="font-size: 0.8rem; {{ $day['is_holiday'] ? 'color: #fca5a5;' : 'color: var(--text-muted);' }}">{{ \Carbon\Carbon::parse($day['date'])->format('d/m/Y') }}</div>
                                    @if($day['is_holiday'])
                                        <div style="background: #ef4444; color: white; padding: 0.1rem 0.5rem; border-radius: 0.25rem; font-size: 0.7rem; margin-top: 0.5rem; display: inline-block;">
                                            {{ $day['holiday_name'] }}
                                        </div>
                                    @endif
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($timeSlots as $slot)
                            <tr>
                                <td style="padding: 1rem; border-bottom: 1px solid var(--border); font-weight: bold; width: 150px;">
                                    {{ $slot->name }}<br>
                                    <span style="font-size: 0.8rem; color: var(--text-muted);">{{ \Carbon\Carbon::parse($slot->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($slot->end_time)->format('H:i') }}</span>
                                </td>
                                @foreach($days as $day)
                                    @php
                                        // Check if there is a booking
                                        $booking = collect($bookings)->first(function($b) use ($slot, $day) {
                                            return $b->time_slot_id == $slot->id && $b->date == $day['date'];
                                        });
                                    @endphp
                                    <td style="padding: 0.5rem; border-bottom: 1px solid var(--border); border-left: 1px solid rgba(255,255,255,0.02); text-align: center; vertical-align: top;">
                                        @if($day['is_holiday'])
                                            <div style="background: rgba(239, 68, 68, 0.05); height: 100%; display: flex; align-items: center; justify-content: center; min-height: 60px; border-radius: 0.5rem; color: #ef4444; opacity: 0.6; font-size: 0.8rem;">
                                                Festivo
                                            </div>
                                        @elseif($booking)
                                            <div style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3); padding: 0.5rem; border-radius: 0.5rem; position: relative;">
                                                <strong style="color: #f87171; display: block; font-size: 0.9rem;">{{ $booking->user->name }}</strong>
                                                @if($booking->observations)
                                                    <p style="font-size: 0.8rem; color: var(--text-muted); margin: 0.2rem 0 0 0;">{{ $booking->observations }}</p>
                                                @endif
                                                
                                                @if($booking->user_id == auth()->id() || auth()->user()->hasRole('admin|directiva'))
                                                    <div style="margin-top: 0.5rem; display: flex; gap: 0.5rem; justify-content: center; align-items: center;">
                                                        <button type="button" onclick="openEditBookingModal({{ $booking->id }}, this.getAttribute('data-obs'))" data-obs="{{ $booking->observations }}" style="background: none; border: none; color: #60a5fa; font-size: 0.8rem; cursor: pointer; text-decoration: underline; padding: 0;">Editar</button>
                                                        <span style="color: rgba(255,255,255,0.2);">|</span>
                                                        <form action="{{ route('tic-bookings.destroy', $booking->id) }}" method="POST" style="margin: 0;">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" onclick="return confirm('¿Cancelar reserva?')" style="background: none; border: none; color: #ef4444; font-size: 0.8rem; cursor: pointer; text-decoration: underline; padding: 0;">Cancelar</button>
                                                        </form>
                                                    </div>
                                                @endif
                                            </div>
                                        @else
                                            <div 
                                                onclick="openBookingModal('{{ $day['date'] }}', {{ $slot->id }}, '{{ $slot->name }}', '{{ \Carbon\Carbon::parse($day['date'])->format('d/m/Y') }}')"
                                                style="background: rgba(255,255,255,0.02); padding: 1rem 0.5rem; border-radius: 0.5rem; cursor: pointer; transition: all 0.2s; min-height: 60px; display: flex; align-items: center; justify-content: center;"
                                                onmouseover="this.style.background='rgba(59, 130, 246, 0.1)'; this.style.color='#60a5fa';"
                                                onmouseout="this.style.background='rgba(255,255,255,0.02)'; this.style.color='var(--text-color)';"
                                            >
                                                <span style="font-size: 0.8rem; opacity: 0.5;">Reservar</span>
                                            </div>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        @elseif($view === 'month')
            <div style="display: grid; grid-template-columns: repeat(7, 1fr); text-align: center; font-weight: bold; color: var(--text-muted); margin-bottom: 0.5rem; gap: 0.5rem;">
                <div>Lu</div><div>Ma</div><div>Mi</div><div>Ju</div><div>Vi</div><div>Sa</div><div>Do</div>
            </div>
            <div style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 0.5rem;">
                @php
                    $firstDay = $days[0];
                    $padDays = ($firstDay['dayOfWeek'] + 6) % 7;
                @endphp
                
                @for($i = 0; $i < $padDays; $i++)
                    <div style="background: transparent; border-radius: 0.5rem; min-height: 100px;"></div>
                @endfor

                @foreach($days as $day)
                    <div style="background: {{ $day['is_holiday'] ? 'rgba(239, 68, 68, 0.05)' : 'rgba(255,255,255,0.03)' }}; border: 1px solid {{ $day['is_holiday'] ? 'rgba(239, 68, 68, 0.3)' : 'var(--border)' }}; border-radius: 0.5rem; min-height: 100px; padding: 0.5rem; {{ $day['is_today'] ? 'border-color: var(--primary);' : '' }}">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                            <span style="{{ $day['is_today'] ? 'background: var(--primary); color: white; padding: 0.1rem 0.4rem; border-radius: 50%; font-size: 0.8rem;' : ($day['is_holiday'] ? 'color: #ef4444; font-weight: bold; font-size: 0.8rem;' : 'color: var(--text-muted); font-size: 0.8rem;') }}">{{ $day['dayNumber'] }}</span>
                            @if(!$day['is_holiday'])
                            <a href="{{ route('tic-bookings.index', ['resource_id' => $selectedResource->id, 'view' => 'day', 'date' => $day['date']]) }}" style="font-size: 0.8rem; color: var(--primary); text-decoration: underline;">Ver Día</a>
                            @endif
                        </div>
                        
                        @if($day['is_holiday'])
                            <div style="background: #ef4444; color: white; padding: 0.2rem 0.4rem; border-radius: 0.25rem; font-size: 0.7rem; text-align: center; margin-bottom: 0.5rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $day['holiday_name'] }}">
                                {{ $day['holiday_name'] }}
                            </div>
                        @else
                            <div style="display: flex; flex-direction: column; gap: 0.2rem;">
                                @foreach($timeSlots as $slot)
                                    @php
                                        $booking = collect($bookings)->first(function($b) use ($slot, $day) {
                                            return $b->time_slot_id == $slot->id && $b->date == $day['date'];
                                        });
                                    @endphp
                                    @if($booking)
                                        <div style="background: rgba(239, 68, 68, 0.1); border-left: 2px solid #ef4444; font-size: 0.7rem; padding: 0.2rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; color: #fca5a5;" title="{{ $booking->user->name }}">
                                            {{ \Carbon\Carbon::parse($slot->start_time)->format('H:i') }} Reservado
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

        @elseif($view === 'year')
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 1.5rem;">
                @foreach($days as $monthData)
                    <div style="background: rgba(255,255,255,0.02); border: 1px solid var(--border); border-radius: 0.5rem; padding: 1rem;">
                        <h4 style="text-align: center; color: var(--primary); margin-bottom: 1rem; text-transform: capitalize;">
                            <a href="{{ route('tic-bookings.index', ['resource_id' => $selectedResource->id, 'view' => 'month', 'date' => $monthData['date']]) }}">{{ $monthData['monthName'] }}</a>
                        </h4>
                        <div style="display: grid; grid-template-columns: repeat(7, 1fr); text-align: center; font-size: 0.7rem; color: var(--text-muted); margin-bottom: 0.5rem;">
                            <div>L</div><div>M</div><div>X</div><div>J</div><div>V</div><div>S</div><div>D</div>
                        </div>
                        <div style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 2px;">
                            @for($i = 0; $i < $monthData['firstDayOfWeek']; $i++)
                                <div></div>
                            @endfor
                            
                            @foreach($monthData['days'] as $day)
                                @php
                                    $hasBooking = collect($bookings)->contains('date', $day['date']);
                                @endphp
                                @if($day['is_holiday'])
                                    <div title="{{ $day['holiday_name'] }}" style="text-align: center; font-size: 0.8rem; padding: 0.2rem 0; border-radius: 0.2rem; background: #ef4444; color: white;">
                                        {{ $day['dayNumber'] }}
                                    </div>
                                @else
                                    <a href="{{ route('tic-bookings.index', ['resource_id' => $selectedResource->id, 'view' => 'day', 'date' => $day['date']]) }}" 
                                       style="text-align: center; font-size: 0.8rem; padding: 0.2rem 0; border-radius: 0.2rem; text-decoration: none; color: white;
                                              {{ $day['is_today'] ? 'border: 1px solid var(--primary);' : '' }}
                                              {{ $hasBooking ? 'background: rgba(239, 68, 68, 0.2); color: #fca5a5;' : 'background: rgba(255,255,255,0.05);' }}
                                    ">
                                        {{ $day['dayNumber'] }}
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    @if($view === 'day' || $view === 'week')
    <!-- Booking Modal -->
    <div id="bookingModal" class="modal" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); backdrop-filter: blur(4px);">
        <div class="modal-content card" style="margin: 10% auto; width: 100%; max-width: 500px; padding: 2rem;">
            <h2 style="margin-bottom: 1.5rem; color: white;">Nueva Reserva</h2>
            <form action="{{ route('tic-bookings.store') }}" method="POST">
                @csrf
                <input type="hidden" name="tic_resource_id" value="{{ $selectedResource->id }}">
                <input type="hidden" name="date" id="modal_date">
                <input type="hidden" name="time_slot_id" id="modal_time_slot_id">
                
                <div style="margin-bottom: 1rem;">
                    <p style="color: var(--text-muted);"><strong>Recurso:</strong> {{ $selectedResource->name }}</p>
                    <p style="color: var(--text-muted);"><strong>Fecha:</strong> <span id="display_date"></span></p>
                    <p style="color: var(--text-muted);"><strong>Tramo:</strong> <span id="display_slot"></span></p>
                </div>

                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label for="observations" style="display: block; margin-bottom: 0.5rem; color: white;">Observaciones (Opcional)</label>
                    <textarea name="observations" id="observations" rows="3" style="width: 100%; background: #1f2937; border: 1px solid var(--border); border-radius: 0.5rem; padding: 0.75rem; color: white;"></textarea>
                </div>
                <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                    <button type="button" class="btn" style="background: transparent; border: 1px solid var(--border); color: white;" onclick="closeBookingModal()">Cancelar</button>
                    <button type="submit" class="btn btn-primary" style="background: var(--primary); border: none; color: white; padding: 0.5rem 1.5rem; border-radius: 0.5rem; cursor: pointer;">Reservar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Booking Modal -->
    <div id="editBookingModal" class="modal" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); backdrop-filter: blur(4px);">
        <div class="modal-content card" style="margin: 10% auto; width: 100%; max-width: 500px; padding: 2rem;">
            <h2 style="margin-bottom: 1.5rem; color: white;">Editar Reserva</h2>
            <form id="editBookingForm" method="POST">
                @csrf
                @method('PUT')
                
                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label for="edit_observations" style="display: block; margin-bottom: 0.5rem; color: white;">Observaciones</label>
                    <textarea name="observations" id="edit_observations" rows="3" style="width: 100%; background: #1f2937; border: 1px solid var(--border); border-radius: 0.5rem; padding: 0.75rem; color: white;"></textarea>
                </div>
                <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                    <button type="button" class="btn" style="background: transparent; border: 1px solid var(--border); color: white;" onclick="closeEditBookingModal()">Cancelar</button>
                    <button type="submit" class="btn btn-primary" style="background: var(--primary); border: none; color: white; padding: 0.5rem 1.5rem; border-radius: 0.5rem; cursor: pointer;">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openBookingModal(date, slotId, slotName, displayDate) {
            document.getElementById('modal_date').value = date;
            document.getElementById('modal_time_slot_id').value = slotId;
            document.getElementById('display_date').textContent = displayDate;
            document.getElementById('display_slot').textContent = slotName;
            document.getElementById('bookingModal').style.display = 'block';
        }

        function closeBookingModal() {
            document.getElementById('bookingModal').style.display = 'none';
        }

        function openEditBookingModal(bookingId, observations) {
            document.getElementById('editBookingForm').action = '/tic-bookings/' + bookingId;
            document.getElementById('edit_observations').value = observations || '';
            document.getElementById('editBookingModal').style.display = 'block';
        }

        function closeEditBookingModal() {
            document.getElementById('editBookingModal').style.display = 'none';
        }

        window.onclick = function(event) {
            const modal = document.getElementById('bookingModal');
            const editModal = document.getElementById('editBookingModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
            if (event.target == editModal) {
                editModal.style.display = 'none';
            }
        }
    </script>
    @endif
@else
    <div class="card" style="text-align: center; padding: 3rem;">
        <p style="color: var(--text-muted); font-size: 1.2rem;">Por favor, selecciona un recurso para ver su disponibilidad y realizar reservas.</p>
    </div>
@endif

@endsection

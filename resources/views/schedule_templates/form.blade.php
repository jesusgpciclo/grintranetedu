@extends('layouts.app')

@section('title', isset($template) ? 'Editar Plantilla' : 'Nueva Plantilla')

@section('content')
    <div class="page-header">
        <h1 class="page-title">{{ isset($template) ? 'Editar Plantilla' : 'Crear Nueva Plantilla' }}</h1>
        <div style="display: flex; gap: 1rem;">
            <a href="{{ route('schedule-templates.index') }}" class="btn" style="background: rgba(255, 255, 255, 0.1);">Volver</a>
        </div>
    </div>

    <div class="card" style="padding: 2rem;">
        <form id="templateForm" method="POST" action="{{ isset($template) ? route('schedule-templates.update', $template->id) : route('schedule-templates.store') }}">
            @csrf
            @if(isset($template))
                @method('PUT')
            @endif

            <div style="margin-bottom: 1.5rem;">
                <label for="name" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Nombre de la Plantilla</label>
                <input type="text" name="name" id="name" class="form-control" style="width: 100%;" value="{{ old('name', $template->name ?? '') }}" required>
            </div>

            <div style="margin-bottom: 1.5rem;">
                <label for="description" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Descripción (Opcional)</label>
                <textarea name="description" id="description" class="form-control" style="width: 100%; min-height: 80px;">{{ old('description', $template->description ?? '') }}</textarea>
            </div>

            <div style="margin-bottom: 2rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Días Activos</label>
                <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                    @php
                        $days = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
                        $activeDays = old('active_days', $template->active_days ?? []);
                    @endphp
                    @foreach($days as $index => $day)
                        <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                            <input type="checkbox" name="active_days[]" value="{{ $index + 1 }}" onchange="updateGrid()"
                                {{ in_array($index + 1, $activeDays) ? 'checked' : '' }} style="width: 1.2rem; height: 1.2rem;">
                            <span>{{ $day }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div style="display: flex; gap: 2rem; flex-wrap: wrap;">
                <!-- Slots Manager -->
                <div style="flex: 1; min-width: 300px;">
                    <h3 style="margin-bottom: 1rem; font-size: 1.1rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem;">Tramos Horarios</h3>
                    <div id="slots-container" style="display: flex; flex-direction: column; gap: 1rem;">
                        <!-- Slots will be injected here via JS -->
                    </div>
                    <button type="button" class="btn btn-primary" onclick="addSlot()" style="margin-top: 1rem; width: 100%;">+ Añadir Tramo</button>
                </div>

                <!-- Live Preview -->
                <div style="flex: 2; min-width: 400px;">
                    <h3 style="margin-bottom: 1rem; font-size: 1.1rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem;">Vista Previa del Horario</h3>
                    <div class="table-container" style="border-radius: 8px; border: 1px solid var(--border-color); overflow: hidden;">
                        <table class="smart-table" id="gridTable" style="margin: 0; width: 100%;">
                            <thead>
                                <tr id="gridHeader">
                                    <th style="width: 140px;">Horario</th>
                                    <!-- Days headers injected via JS -->
                                </tr>
                            </thead>
                            <tbody id="gridBody">
                                <!-- Grid rows injected via JS -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div style="margin-top: 2.5rem; display: flex; justify-content: flex-end;">
                <button type="submit" class="btn btn-primary">Guardar Plantilla</button>
            </div>
        </form>
    </div>

    <!-- Template for JS -->
    <template id="slot-template">
        <div class="slot-item" data-index="{index}" style="background: var(--bg-secondary); border: 1px solid var(--border-color); padding: 1rem; border-radius: 8px; position: relative;">
            <div style="display: flex; justify-content: space-between; margin-bottom: 0.8rem; align-items: center;">
                <span class="slot-title" style="font-weight: 600; font-size: 0.9rem;">Tramo {number}</span>
                <button type="button" class="btn btn-danger" onclick="removeSlot(this)" style="padding: 0.2rem 0.5rem; font-size: 0.8rem;">×</button>
            </div>
            <div>
                <input type="text" name="slots[{index}][name]" class="form-control slot-name" placeholder="Nombre (ej. 1ª Hora)" oninput="updateGrid()" required style="width: 100%; margin-bottom: 0.8rem; background: var(--bg-primary); border: 1px solid var(--border-color);">
                <div style="display: flex; gap: 0.5rem; align-items: center;">
                    <input type="time" name="slots[{index}][start_time]" class="form-control slot-start" onchange="updateGrid()" required style="width: 100%; background: var(--bg-primary); border: 1px solid var(--border-color);">
                    <span>-</span>
                    <input type="time" name="slots[{index}][end_time]" class="form-control slot-end" onchange="updateGrid()" required style="width: 100%; background: var(--bg-primary); border: 1px solid var(--border-color);">
                </div>
            </div>
        </div>
    </template>

    <script>
        let slotCount = 0;
        const existingSlots = @json($template->timeSlots ?? []);
        const days = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];

        document.addEventListener('DOMContentLoaded', () => {
            if (existingSlots.length > 0) {
                existingSlots.forEach(slot => {
                    addSlot(slot);
                });
            } else {
                // Add one empty slot by default
                addSlot();
            }
            updateGrid();
        });

        function addSlot(data = null) {
            const template = document.getElementById('slot-template').innerHTML;
            const html = template
                .replace(/{index}/g, slotCount)
                .replace(/{number}/g, slotCount + 1);
            
            const container = document.getElementById('slots-container');
            const div = document.createElement('div');
            div.innerHTML = html;
            const slotEl = div.firstElementChild;
            
            container.appendChild(slotEl);

            if (data) {
                slotEl.querySelector('.slot-name').value = data.name;
                slotEl.querySelector('.slot-start').value = data.start_time ? data.start_time.substring(0, 5) : '';
                slotEl.querySelector('.slot-end').value = data.end_time ? data.end_time.substring(0, 5) : '';
            }

            slotCount++;
            reindexSlots();
            updateGrid();
        }

        function removeSlot(btn) {
            btn.closest('.slot-item').remove();
            reindexSlots();
            updateGrid();
        }

        function reindexSlots() {
            const slots = document.querySelectorAll('.slot-item');
            slots.forEach((slot, index) => {
                slot.querySelector('.slot-title').textContent = `Tramo ${index + 1}`;
            });
        }

        function updateGrid() {
            // 1. Get Active Days
            const activeDaysChecks = document.querySelectorAll('input[name="active_days[]"]:checked');
            const activeIndices = Array.from(activeDaysChecks).map(cb => parseInt(cb.value));
            activeIndices.sort();

            // 2. Update Header
            const headerRow = document.getElementById('gridHeader');
            // Clear existing headers except first
            while (headerRow.children.length > 1) {
                headerRow.removeChild(headerRow.lastChild);
            }
            
            if (activeIndices.length === 0) {
                 const th = document.createElement('th');
                 th.textContent = 'Seleccione días activos';
                 th.style.color = 'var(--text-muted)';
                 th.style.fontWeight = 'normal';
                 headerRow.appendChild(th);
            } else {
                activeIndices.forEach(idx => {
                    const th = document.createElement('th');
                    th.textContent = days[idx - 1];
                    headerRow.appendChild(th);
                });
            }

            // 3. Get Slots Data
            const slots = [];
            document.querySelectorAll('.slot-item').forEach(item => {
                slots.push({
                    name: item.querySelector('.slot-name').value,
                    start: item.querySelector('.slot-start').value,
                    end: item.querySelector('.slot-end').value
                });
            });

            // 4. Update Body
            const body = document.getElementById('gridBody');
            body.innerHTML = '';

            if (slots.length === 0) {
                 const tr = document.createElement('tr');
                 const td = document.createElement('td');
                 td.colSpan = activeIndices.length > 0 ? activeIndices.length + 1 : 2;
                 td.textContent = 'Añada tramos horarios';
                 td.style.textAlign = 'center';
                 td.style.color = 'var(--text-muted)';
                 td.style.padding = '2rem';
                 tr.appendChild(td);
                 body.appendChild(tr);
                 return;
            }

            slots.forEach(slot => {
                const tr = document.createElement('tr');
                
                // Time Column
                const tdTime = document.createElement('td');
                const start = slot.start ? slot.start.substring(0, 5) : '--:--';
                const end = slot.end ? slot.end.substring(0, 5) : '--:--';
                tdTime.innerHTML = `<strong style="color: var(--primary);">${start} - ${end}</strong><br><small style="color: var(--text-muted);">${slot.name || 'Sin nombre'}</small>`;
                tr.appendChild(tdTime);

                if (activeIndices.length === 0) {
                     const td = document.createElement('td');
                     tr.appendChild(td);
                } else {
                    // Day Columns
                    activeIndices.forEach(() => {
                        const td = document.createElement('td');
                        td.style.background = 'rgba(255,255,255,0.02)';
                        tr.appendChild(td);
                    });
                }

                body.appendChild(tr);
            });
        }

        document.getElementById('templateForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = data.redirect;
                } else {
                    alert('Error: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred. Check the console.');
            });
        });

    </script>
@endsection

<?php

namespace App\Livewire;

use App\Models\Aula;
use App\Models\Ausencia;
use App\Models\TimeSlot;
use App\Models\User;
use App\Services\GuardiaEquityService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

/**
 * Componente Livewire Senior para la gestión reactiva en tiempo real del Parte de Guardia.
 * Incorpora polling periódico (wire:poll.15s), algoritmo de equidad docente con desempate temporal,
 * exclusión automática de docentes ausentes y confirmación de guardias a 1 clic.
 */
class ParteDiario
{
    public string $date;
    public string|int $filterSlotId = 'all';
    public array $openSlots = [];
    public array $guardiaAssignments = [];

    protected GuardiaEquityService $equityService;

    public function boot(GuardiaEquityService $equityService): void
    {
        $this->equityService = $equityService;
    }

    public function mount(?string $date = null): void
    {
        $this->date = $date ?? date('Y-m-d');
        $this->initializeOpenSlots();
    }

    /**
     * Abre por defecto el tramo en curso y cualquier tramo con ausencias registradas.
     */
    public function initializeOpenSlots(): void
    {
        $currentSlotId = $this->getCurrentTimeSlotId();
        $timeSlots = TimeSlot::orderBy('order')->get();

        foreach ($timeSlots as $slot) {
            $hasAusencias = Ausencia::whereDate('fecha', $this->date)
                ->where('time_slot_id', $slot->id)
                ->exists();

            if ($hasAusencias || $slot->id === $currentSlotId) {
                $this->openSlots[$slot->id] = true;
            } else {
                $this->openSlots[$slot->id] = false;
            }
        }
    }

    /**
     * Conmuta el estado expandido/colapsado del acordeón inteligente de un tramo.
     */
    public function toggleSlotAccordion(int $slotId): void
    {
        $this->openSlots[$slotId] = !($this->openSlots[$slotId] ?? false);
    }

    /**
     * Filtra la vista para mostrar únicamente un tramo específico o todos ('all').
     */
    public function filtrarHora(string|int $slotId): void
    {
        $this->filterSlotId = $slotId;
        if ($slotId !== 'all') {
            $this->openSlots[(int)$slotId] = true;
        }
    }

    /**
     * Confirma la cobertura de una guardia asignando al docente seleccionado.
     */
    public function confirmarGuardia(int $ausenciaId, ?int $guardiaUserId = null): void
    {
        $ausencia = Ausencia::findOrFail($ausenciaId);
        $user = Auth::user();

        // Obtener el docente asignado desde el parámetro o el estado del formulario
        $assignedId = $guardiaUserId ?? ($this->guardiaAssignments[$ausenciaId] ?? null);

        // Si no se especificó un docente, usar el recomendado por equidad
        if (!$assignedId) {
            $disponibles = $this->getAvailableGuardiasForSlot($ausencia->time_slot_id);
            $recommended = $disponibles->firstWhere('is_recommended', true) ?? $disponibles->first();
            $assignedId = $recommended['user_id'] ?? null;
        }

        // Si aún no hay asignado, asignarse a sí mismo si tiene permiso
        if (!$assignedId && $user) {
            $assignedId = $user->id;
        }

        if ($assignedId) {
            $ausencia->update([
                'guardia_user_id' => $assignedId,
                'guardia_confirmed_at' => now(),
            ]);

            session()->flash('message', '✓ Guardia confirmada con éxito.');
        }
    }

    /**
     * Asignación inmediata a 1 clic para el docente en turno de guardia autenticado.
     */
    public function asignarmeYo(int $ausenciaId): void
    {
        if ($user = Auth::user()) {
            $this->confirmarGuardia($ausenciaId, $user->id);
        }
    }

    /**
     * Libera una guardia previamente confirmada (desconfirmación).
     */
    public function desconfirmarGuardia(int $ausenciaId): void
    {
        $ausencia = Ausencia::findOrFail($ausenciaId);
        $user = Auth::user();

        if ($ausencia->guardia_user_id === $user?->id || $user?->hasAnyRole(['admin', 'directiva'])) {
            $ausencia->update([
                'guardia_user_id' => null,
                'guardia_confirmed_at' => null,
            ]);

            session()->flash('message', 'Guardia liberada correctamente.');
        }
    }

    /**
     * Elimina una ausencia del parte (si el usuario tiene permisos).
     */
    public function eliminarAusencia(int $ausenciaId): void
    {
        $ausencia = Ausencia::findOrFail($ausenciaId);
        if ($ausencia->canBeDeletedBy(Auth::user())) {
            $ausencia->delete();
            session()->flash('message', 'Ausencia eliminada del parte.');
        }
    }

    /**
     * Retorna los docentes de guardia disponibles para un tramo concreto,
     * ordenados estrictamente por el algoritmo de equidad (menos horas primero,
     * desempate por fecha más antigua de última guardia, y exclusión de ausentes).
     */
    public function getAvailableGuardiasForSlot(int $timeSlotId): Collection
    {
        return $this->equityService->getAvailableGuardiasForSlot($this->date, $timeSlotId);
    }

    /**
     * Retorna los docentes de guardia de este tramo que han registrado ausencia.
     */
    public function getAbsentGuardiasForSlot(int $timeSlotId): Collection
    {
        return $this->equityService->getAbsentGuardiasForSlot($this->date, $timeSlotId);
    }

    /**
     * Detecta el tramo horario activo en el momento actual según la hora del sistema.
     */
    public function getCurrentTimeSlotId(): ?int
    {
        $now = now()->format('H:i:s');
        return TimeSlot::where('start_time', '<=', $now)
            ->where('end_time', '>=', $now)
            ->value('id');
    }

    public function render()
    {
        $carbonDate = Carbon::parse($this->date);
        $isToday = $carbonDate->isToday();

        $timeSlots = TimeSlot::orderBy('order')->get();

        $ausenciasQuery = Ausencia::whereDate('fecha', $this->date)
            ->with(['user', 'guardiaUser', 'group', 'zona']);

        $allAusencias = $ausenciasQuery->get();
        $groupedAusencias = $allAusencias->groupBy('time_slot_id');

        $totalDay = $allAusencias->count();
        $cubiertasDay = $allAusencias->filter(fn($a) => $a->isCubierta())->count();
        $sinCubrirDay = $totalDay - $cubiertasDay;

        $aulas = Aula::orderBy('nombre')->get();
        $teachers = User::orderBy('name')->get();

        return view('livewire.parte-diario', [
            'carbonDate' => $carbonDate,
            'isToday' => $isToday,
            'timeSlots' => $timeSlots,
            'ausencias' => $groupedAusencias,
            'totalDay' => $totalDay,
            'cubiertasDay' => $cubiertasDay,
            'sinCubrirDay' => $sinCubrirDay,
            'currentSlotId' => $this->getCurrentTimeSlotId(),
            'aulas' => $aulas,
            'teachers' => $teachers,
        ]);
    }
}

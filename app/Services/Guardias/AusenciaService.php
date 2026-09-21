<?php

declare(strict_types=1);

namespace App\Services\Guardias;

use App\Events\AusenciaRegistradaEvent;
use App\Exceptions\AusenciaOperationException;
use App\Models\Ausencia;
use App\Models\ScheduleSelection;
use App\Models\User;
use App\Services\GuardiaEquityService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Servicio de Dominio para la Gestión del Ciclo de Vida de Ausencias y Faltas Docentes.
 *
 * Encapsula la lógica de negocio, validación de permisos, integración con el motor
 * de equidad de guardias y emisión de eventos en tiempo real sin acoplarse al resto de la aplicación.
 */
class AusenciaService
{
    public function __construct(
        protected GuardiaEquityService $equityService
    ) {}

    /**
     * Registra una nueva ausencia docente en el sistema.
     *
     * - Verifica si el docente tenía asignada guardia o convivencia en esa franja; si es así,
     *   marca la ausencia como 'es_guardia' para que quede automáticamente no disponible
     *   y el motor de equidad no lo asigne como sustituto a otro grupo.
     * - Emite el evento AusenciaRegistradaEvent para actualización en tiempo real del Parte de Guardia.
     *
     * @param array<string, mixed> $datos Atributos de la ausencia a registrar.
     * @return Ausencia La instancia de ausencia creada.
     * @throws Throwable Si ocurre un fallo en la base de datos o durante la transacción.
     */
    public function registrarAusencia(array $datos): Ausencia
    {
        return DB::transaction(function () use ($datos): Ausencia {
            $userId = (int) ($datos['user_id'] ?? 0);
            $timeSlotId = (int) ($datos['time_slot_id'] ?? 0);
            $fechaStr = (string) ($datos['fecha'] ?? now()->format('Y-m-d'));

            $carbonDate = Carbon::parse($fechaStr);
            $dayOfWeekNumber = $carbonDate->dayOfWeekIso; // 1 (Lunes) a 7 (Domingo)
            $dayOfWeekName = GuardiaEquityService::normalizeDay($dayOfWeekNumber);

            // 1. Verificar si el docente ausente tenía asignada una hora de guardia o convivencia en esta hora
            $hasGuardiaScheduled = ScheduleSelection::where('time_slot_id', $timeSlotId)
                ->whereHas('userSchedule', function ($query) use ($userId): void {
                    $query->where('user_id', $userId);
                })
                ->where(function ($query) use ($dayOfWeekNumber, $dayOfWeekName): void {
                    $query->where('day', (string) $dayOfWeekNumber)
                        ->orWhereRaw('LOWER(day) = ?', [$dayOfWeekName]);
                })
                ->where(function ($query): void {
                    $query->whereNotNull('guardia_id')
                        ->orWhere('value', 'LIKE', '%Guardia%')
                        ->orWhere('is_convivencia', true);
                })
                ->exists();

            if ($hasGuardiaScheduled) {
                // Se marca formalmente como ausencia de guardia para excluirlo de la disponibilidad
                $datos['es_guardia'] = true;
            }

            // 2. Persistir el registro en la base de datos
            $ausencia = Ausencia::create($datos);

            // 3. Cargar relaciones requeridas para el payload del evento
            $ausencia->loadMissing(['user', 'timeSlot', 'group', 'zona']);

            // 4. Disparar evento para notificación en tiempo real al panel de "Parte de Guardia"
            event(new AusenciaRegistradaEvent($ausencia));

            Log::info("Ausencia registrada ID {$ausencia->id} para el usuario {$userId} en tramo {$timeSlotId} fecha {$fechaStr}");

            return $ausencia;
        });
    }

    /**
     * Elimina una ausencia del sistema aplicando las políticas de negocio y liberando sustitutos.
     *
     * Reglas aplicadas:
     * - Un docente ordinario solo puede eliminar sus propias ausencias.
     * - No se puede eliminar si la hora de inicio del tramo ya ha comenzado.
     * - Si la ausencia ya tiene un sustituto asignado ('cubierta' o 'firmada'), solo 'admin' o 'directiva' pueden eliminarla.
     * - Si tenía sustituto pre-asignado, libera a dicho docente y refresca la cola de equidad.
     *
     * @param int $ausenciaId Identificador primario de la ausencia.
     * @param User $usuario Usuario que intenta ejecutar la operación.
     * @return bool Verdadero si la eliminación fue exitosa.
     * @throws AusenciaOperationException Si se infringe alguna política o permiso.
     * @throws Throwable Si ocurre un error durante la transacción.
     */
    public function eliminarAusencia(int $ausenciaId, User $usuario): bool
    {
        return DB::transaction(function () use ($ausenciaId, $usuario): bool {
            /** @var Ausencia $ausencia */
            $ausencia = Ausencia::with(['guardiaUser', 'timeSlot'])->findOrFail($ausenciaId);

            $isDirectiva = $usuario->hasAnyRole(['admin', 'directiva', 'directivo']);

            // 1. Validación: si la ausencia ya está cubierta o firmada por un docente
            if ($ausencia->isCubierta() && !$isDirectiva) {
                throw new AusenciaOperationException(
                    "No se puede eliminar la ausencia porque ya ha sido cubierta o firmada por un docente de guardia. Solo el equipo directivo o administración pueden autorizar su anulación."
                );
            }

            // 2. Validación de propiedad de la ausencia
            if (!$isDirectiva && $ausencia->user_id !== $usuario->id) {
                throw new AusenciaOperationException(
                    "No tienes autorización para eliminar una ausencia que pertenece a otro docente."
                );
            }

            // 3. Validación de inicio del tramo lectivo
            if (!$isDirectiva && $ausencia->hasStarted()) {
                throw new AusenciaOperationException(
                    "No es posible eliminar la ausencia porque la hora lectiva ya ha dado comienzo."
                );
            }

            // 4. Si la ausencia tenía un profesor sustituto pre-asignado, liberarlo y recalcular prioridad
            $sustitutoId = $ausencia->guardia_user_id;
            if ($sustitutoId !== null) {
                $ausencia->update([
                    'guardia_user_id' => null,
                    'guardia_confirmed_at' => null,
                ]);

                // Recalcular / refrescar la disponibilidad y equidad en el tramo afectado
                if ($ausencia->fecha && $ausencia->time_slot_id) {
                    $fechaStr = $ausencia->fecha->format('Y-m-d');
                    $this->equityService->getAvailableGuardiasForSlot($fechaStr, $ausencia->time_slot_id);
                }

                Log::info("Docente sustituto ID {$sustitutoId} liberado de la ausencia ID {$ausenciaId}");
            }

            // 5. Eliminar el registro definitivamente
            $ausencia->delete();

            Log::info("Ausencia ID {$ausenciaId} eliminada correctamente por el usuario ID {$usuario->id}");

            return true;
        });
    }
}

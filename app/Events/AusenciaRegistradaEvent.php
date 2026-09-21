<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Ausencia;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Evento emitido al registrar una nueva ausencia docente en el sistema.
 * Notifica en tiempo real (WebSockets / Broadcast) al panel del "Parte de Guardia".
 */
class AusenciaRegistradaEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Ausencia $ausencia
    ) {}

    /**
     * Canales por los que se difunde el evento.
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('parte-de-guardia'),
        ];
    }

    /**
     * Nombre público del evento emitido al frontend.
     */
    public function broadcastAs(): string
    {
        return 'ausencia.registrada';
    }

    /**
     * Payload optimizado enviado a los clientes suscritos.
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->ausencia->id,
            'user_id' => $this->ausencia->user_id,
            'docente' => trim(($this->ausencia->user?->name ?? 'Docente') . ' ' . ($this->ausencia->user?->last_name ?? '')),
            'fecha' => $this->ausencia->fecha ? $this->ausencia->fecha->format('Y-m-d') : null,
            'time_slot_id' => $this->ausencia->time_slot_id,
            'tramo' => $this->ausencia->timeSlot?->name,
            'grupo' => $this->ausencia->group ? ($this->ausencia->group->course . ' ' . $this->ausencia->group->name) : null,
            'aula' => $this->ausencia->zona?->nombre,
            'tarea' => $this->ausencia->tarea,
            'es_guardia' => $this->ausencia->es_guardia,
        ];
    }
}

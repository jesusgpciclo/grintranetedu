<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use App\Models\Aula;

class Ausencia extends Model
{
    protected $fillable = [
        'user_id',
        'fecha',
        'time_slot_id',
        'group_id',
        'zona_id',
        'tarea',
        'guardia_user_id',
        'guardia_confirmed_at',
        'enlace_tarea',
        'observaciones_guardia',
        'es_guardia',
        'justificada',
        'justificacion_nota',
    ];

    protected $casts = [
        'fecha' => 'date',
        'guardia_confirmed_at' => 'datetime',
        'es_guardia' => 'boolean',
        'justificada' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function guardiaUser()
    {
        return $this->belongsTo(User::class, 'guardia_user_id');
    }

    public function timeSlot()
    {
        return $this->belongsTo(TimeSlot::class);
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function zona()
    {
        return $this->belongsTo(Aula::class, 'zona_id');
    }

    /**
     * Determine if this absence is covered (confirmed by a substitute teacher).
     */
    public function isCubierta(): bool
    {
        return !empty($this->guardia_user_id) && !empty($this->guardia_confirmed_at);
    }

    /**
     * Check if the absence is happening right now or in the future vs started.
     */
    public function hasStarted(): bool
    {
        if (!$this->timeSlot || !$this->fecha) {
            return false;
        }

        $now = Carbon::now();
        $slotDate = Carbon::parse($this->fecha->format('Y-m-d') . ' ' . $this->timeSlot->start_time);

        return $now->greaterThanOrEqualTo($slotDate);
    }

    /**
     * Check if the current user can delete this absence.
     * Rule: Regular teachers can only delete their own absence if the time slot has not started yet.
     * Directiva/Admins can delete anytime.
     */
    public function canBeDeletedBy($user): bool
    {
        if (!$user) {
            return false;
        }

        if ($user->hasRole('admin') || $user->hasRole('directiva') || $user->hasRole('directivo')) {
            return true;
        }

        // Si ya está cubierta o firmada, solo admin o directivo pueden modificarla o eliminarla
        if ($this->isCubierta()) {
            return false;
        }

        if ($this->user_id === $user->id) {
            return !$this->hasStarted();
        }

        return false;
    }

    /**
     * Check if the current user can edit this absence.
     */
    public function canBeEditedBy($user): bool
    {
        return $this->canBeDeletedBy($user);
    }
}

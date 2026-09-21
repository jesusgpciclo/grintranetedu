<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CalendarEvent extends Model
{
    protected $fillable = [
        'calendar_id',
        'title',
        'type',
        'start_date',
        'end_date',
        'description',
        'attachment',
        'color',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
    ];

    public function getAttachmentUrlAttribute(): ?string
    {
        return $this->attachment ? route('calendar.events.attachment', $this->id) : null;
    }

    public static array $types = [
        'holiday'            => 'Festivo',
        'vacation'           => 'Vacaciones',
        'non_teaching'       => 'Día no lectivo',
        'evaluation'         => 'Evaluación',
        'excursion'          => 'Salida / Excursión',
        'department_meeting' => 'Reunión Depto.',
        'faculty_meeting'    => 'Claustro',
        'other'              => 'Otro',
    ];

    public static array $typeColors = [
        'holiday'            => '#ef4444',
        'vacation'           => '#f97316',
        'non_teaching'       => '#a855f7',
        'evaluation'         => '#3b82f6',
        'excursion'          => '#06b6d4',
        'department_meeting' => '#10b981',
        'faculty_meeting'    => '#f59e0b',
        'other'              => '#6b7280',
    ];

    public function calendar()
    {
        return $this->belongsTo(Calendar::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getTypeColorAttribute(): string
    {
        return $this->color ?? static::$typeColors[$this->type] ?? '#6b7280';
    }

    public function getTypeLabelAttribute(): string
    {
        return static::$types[$this->type] ?? 'Otro';
    }

    /**
     * Check if this event spans a given date.
     */
    public function spansDate(string $date): bool
    {
        $d = \Carbon\Carbon::parse($date);
        $start = $this->start_date;
        $end = $this->end_date ?? $this->start_date;
        return $d->between($start, $end);
    }
}

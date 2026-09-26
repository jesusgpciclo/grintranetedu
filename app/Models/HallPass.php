<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HallPass extends Model
{
    protected $fillable = [
        'user_id',
        'teacher_id',
        'reason',
        'date',
        'start_time',
        'end_time'
    ];

    protected $casts = [
        'date' => 'date',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function getDurationMinutesAttribute(): ?int
    {
        if (!$this->end_time || !$this->start_time) {
            return null;
        }

        $minutes = $this->start_time->diffInMinutes($this->end_time, false);
        return (int) round(abs($minutes));
    }

    public function getDurationFormattedAttribute(): string
    {
        if (!$this->end_time || !$this->start_time) {
            return 'Activo';
        }

        $diffSeconds = abs($this->start_time->diffInSeconds($this->end_time, false));

        if ($diffSeconds < 60) {
            return '< 1 min';
        }

        $minutes = (int) round($diffSeconds / 60);
        return "{$minutes} min";
    }

    public function getTeacherFullNameAttribute(): string
    {
        $teacher = $this->teacher;
        if (!$teacher) {
            return 'N/A';
        }

        $fullName = trim(($teacher->name ?? '') . ' ' . ($teacher->last_name ?? ''));
        return $fullName !== '' ? $fullName : 'N/A';
    }
}

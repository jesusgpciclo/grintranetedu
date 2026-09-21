<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScheduleSelection extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_schedule_id',
        'time_slot_id',
        'day',
        'value',
        'type',
        'subject',
        'group_id',
        'guardia_id',
        'is_convivencia'
    ];

    protected $casts = [
        'is_convivencia' => 'boolean',
    ];

    public function userSchedule()
    {
        return $this->belongsTo(UserSchedule::class);
    }

    public function timeSlot()
    {
        return $this->belongsTo(TimeSlot::class);
    }

    public function guardia()
    {
        return $this->belongsTo(Guardia::class);
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }
}

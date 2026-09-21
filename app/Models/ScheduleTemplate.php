<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScheduleTemplate extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'active_days'];

    protected $casts = [
        'active_days' => 'array',
    ];

    public function timeSlots()
    {
        return $this->hasMany(TimeSlot::class)->orderBy('order')->orderBy('start_time');
    }

    /**
     * Get the active template for guardias.
     */
    public static function getActiveTemplate()
    {
        $activeTemplateId = Setting::get('guardias_schedule_template_id');
        if ($activeTemplateId) {
            $template = self::find($activeTemplateId);
            if ($template) {
                return $template;
            }
        }

        // Fallback to first template
        return self::first();
    }

    /**
     * Get active time slots for guardias.
     */
    public static function getActiveTimeSlots()
    {
        $template = self::getActiveTemplate();
        if ($template) {
            return $template->timeSlots;
        }

        return TimeSlot::orderBy('start_time')->get();
    }
}


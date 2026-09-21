<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSchedule extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'schedule_template_id', 'school_year_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scheduleTemplate()
    {
        return $this->belongsTo(ScheduleTemplate::class);
    }

    public function schoolYear()
    {
        return $this->belongsTo(SchoolYear::class);
    }

    public function selections()
    {
        return $this->hasMany(ScheduleSelection::class);
    }
}

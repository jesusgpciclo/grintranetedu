<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    protected $fillable = ['course', 'name', 'tutor_id', 'school_year_id', 'dificultad'];

    protected $casts = [
        'dificultad' => 'integer',
    ];

    public function schoolYear()
    {
        return $this->belongsTo(SchoolYear::class);
    }

    public function tutor()
    {
        return $this->belongsTo(User::class, 'tutor_id');
    }

    public function students()
    {
        return $this->hasMany(User::class);
    }
}

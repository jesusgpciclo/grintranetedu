<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Incidencia extends Model
{
    protected $fillable = [
        'titulo',
        'descripcion',
        'fecha',
        'prioridad',
        'estado',
        'user_id',
        'recurso_id',
        'school_year_id',
    ];

    public function schoolYear()
    {
        return $this->belongsTo(\App\Models\SchoolYear::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function recurso()
    {
        return $this->belongsTo(Recurso::class);
    }
}

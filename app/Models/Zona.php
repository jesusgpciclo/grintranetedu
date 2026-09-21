<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Zona extends Model
{
    protected $fillable = [
        'nombre',
        'planta',
        'dificultad',
        'aforo',
    ];

    protected $casts = [
        'dificultad' => 'integer',
        'aforo' => 'integer',
    ];
}

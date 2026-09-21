<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Aula extends Model
{
    protected $fillable = ['nombre', 'tipo', 'identificacion', 'capacidad', 'equipamiento', 'ubicacion', 'descripcion', 'dificultad', 'aforo'];

    protected $casts = [
        'equipamiento' => 'array',
        'dificultad' => 'integer',
        'aforo' => 'integer',
    ];

    public function getPlantaAttribute()
    {
        return $this->ubicacion;
    }

    public function setPlantaAttribute($value)
    {
        $this->attributes['ubicacion'] = $value;
    }
}

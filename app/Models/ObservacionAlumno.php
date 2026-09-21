<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ObservacionAlumno extends Model
{
    protected $table = 'observaciones_alumnos';
    protected $fillable = ['alumno_id', 'profesor_id', 'tipo', 'descripcion', 'fecha'];

    protected $casts = [
        'fecha' => 'date',
    ];

    public function alumno()
    {
        return $this->belongsTo(User::class, 'alumno_id');
    }

    public function profesor()
    {
        return $this->belongsTo(User::class, 'profesor_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sesion extends Model
{
    protected $table = 'sesiones';
    protected $fillable = [
        'fecha', 'modulo_id', 'group_id', 'profesor_id',
        'contenidos', 'actividades_realizadas', 'tareas_mandadas',
        'enlaces_recursos', 'observaciones'
    ];

    protected $casts = [
        'fecha' => 'date',
        'enlaces_recursos' => 'array',
    ];

    public function modulo()
    {
        return $this->belongsTo(Modulo::class);
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function profesor()
    {
        return $this->belongsTo(User::class, 'profesor_id');
    }

    public function asistencias()
    {
        return $this->hasMany(Asistencia::class, 'sesion_id');
    }
}

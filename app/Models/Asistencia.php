<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Asistencia extends Model
{
    protected $table = 'asistencias_sesiones';
    protected $fillable = ['sesion_id', 'user_id', 'estado', 'observacion'];

    public function sesion()
    {
        return $this->belongsTo(Sesion::class);
    }

    public function alumno()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

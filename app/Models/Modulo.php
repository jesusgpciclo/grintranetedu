<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Modulo extends Model
{
    protected $fillable = ['codigo', 'nombre', 'group_id', 'horas_semanales', 'descripcion'];

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function profesores()
    {
        return $this->belongsToMany(User::class, 'modulo_user', 'modulo_id', 'user_id');
    }

    public function resultadosAprendizaje()
    {
        return $this->hasMany(ResultadoAprendizaje::class)->orderBy('orden');
    }

    public function actividades()
    {
        return $this->hasMany(Actividad::class);
    }

    public function sesiones()
    {
        return $this->hasMany(Sesion::class);
    }
}

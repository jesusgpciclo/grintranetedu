<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recurso extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre', 
        'tipo_id', 
        'marca', 
        'modelo', 
        'numero_serie', 
        'estado', 
        'ubicacion', 
        'responsable_id'
    ];

    public function tipo()
    {
        return $this->belongsTo(TipoRecurso::class, 'tipo_id');
    }

    public function responsable()
    {
        return $this->belongsTo(User::class, 'responsable_id');
    }
     public function reservas()
    {
        return $this->hasMany(ReservaRecurso::class, 'recurso_id');
    }
    public function incidencias()
    {
        return $this->hasMany(Incidencia::class);
    }
}

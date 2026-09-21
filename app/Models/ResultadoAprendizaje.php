<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResultadoAprendizaje extends Model
{
    protected $table = 'resultados_aprendizaje';
    protected $fillable = ['codigo', 'descripcion', 'peso', 'modulo_id', 'orden'];

    public function modulo()
    {
        return $this->belongsTo(Modulo::class);
    }

    public function criteriosEvaluacion()
    {
        return $this->hasMany(CriterioEvaluacion::class)->orderBy('orden');
    }
}

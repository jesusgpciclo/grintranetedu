<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CriterioEvaluacion extends Model
{
    protected $table = 'criterios_evaluacion';
    protected $fillable = ['codigo', 'descripcion', 'peso', 'resultado_aprendizaje_id', 'orden'];

    public function resultadoAprendizaje()
    {
        return $this->belongsTo(ResultadoAprendizaje::class);
    }

    public function actividades()
    {
        return $this->belongsToMany(Actividad::class, 'actividad_criterio_evaluacion');
    }
}

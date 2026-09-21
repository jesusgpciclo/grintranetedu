<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Actividad extends Model
{
    protected $table = 'actividades';
    protected $fillable = ['titulo', 'descripcion', 'tipo', 'modulo_id', 'criterio_evaluacion_id', 'fecha_entrega', 'peso', 'es_evaluable'];

    protected $casts = [
        'fecha_entrega' => 'date',
        'es_evaluable' => 'boolean',
    ];

    public function modulo()
    {
        return $this->belongsTo(Modulo::class);
    }

    /**
     * Relación legacy (single CE).
     */
    public function criterioEvaluacion()
    {
        return $this->belongsTo(CriterioEvaluacion::class, 'criterio_evaluacion_id');
    }

    /**
     * Relación many-to-many con criterios de evaluación.
     */
    public function criteriosEvaluacion()
    {
        return $this->belongsToMany(CriterioEvaluacion::class, 'actividad_criterio_evaluacion');
    }

    public function notas()
    {
        return $this->hasMany(Nota::class, 'actividad_id');
    }

    public function rubrica()
    {
        return $this->hasOne(Rubrica::class, 'actividad_id');
    }
}

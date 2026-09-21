<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rubrica extends Model
{
    protected $table = 'rubricas';
    protected $fillable = ['nombre', 'descripcion', 'actividad_id'];

    public function actividad()
    {
        return $this->belongsTo(Actividad::class);
    }

    public function criterios()
    {
        return $this->hasMany(CriterioRubrica::class);
    }
}

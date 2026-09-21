<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CriterioRubrica extends Model
{
    protected $table = 'criterios_rubrica';
    protected $fillable = ['rubrica_id', 'nombre', 'descripcion', 'peso', 'niveles'];

    protected $casts = [
        'niveles' => 'array',
    ];

    public function rubrica()
    {
        return $this->belongsTo(Rubrica::class);
    }
}

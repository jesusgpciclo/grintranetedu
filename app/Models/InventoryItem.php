<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'tipo',
        'subtipo',
        'procedencia',
        'estado',
        'descripcion',
        'fecha_alta',
        'precio',
        'num_registro_general',
        'num_serie',
        'edificio',
        'planta',
        'localizacion',
        'dependencia_adscripcion',
        'solicitud_retirada',
        'fecha_baja',
        'motivo_baja',
        'observaciones',
        'school_year_id'
    ];

    public function schoolYear()
    {
        return $this->belongsTo(SchoolYear::class);
    }
}

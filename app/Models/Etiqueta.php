<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\DocumentoInstitucional;

class Etiqueta extends Model
{
    protected $fillable = ['nombre', 'descripcion'];

    public function documentos()
    {
        return $this->belongsToMany(DocumentoInstitucional::class, 'documento_etiqueta', 'etiqueta_id', 'documento_id');
    }
}

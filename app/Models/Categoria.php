<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Categoria extends Model
{
    protected $fillable = ['nombre', 'tipo_recurso_id', 'parent_id'];

    public function parent()
    {
        return $this->belongsTo(Categoria::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Categoria::class, 'parent_id');
    }

    public function childrenRecursive()
    {
        return $this->children()->with(['childrenRecursive', 'documentos']);
    }

    public function tipoRecurso()
    {
        return $this->belongsTo(TipoRecurso::class);
    }

    public function documentos()
    {
        return $this->hasMany(DocumentoInstitucional::class, 'categoria_id');
    }
}

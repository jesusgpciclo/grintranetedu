<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoRecurso extends Model
{
    use HasFactory;

    protected $fillable = ['nombre'];

    public function categorias()
    {
        return $this->hasMany(Categoria::class);
    }

    public function recursos()
    {
        return $this->hasMany(Recurso::class, 'tipo_id');
    }
}
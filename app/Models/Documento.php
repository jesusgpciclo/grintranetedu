<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Documento extends Model
{
    protected $table = 'documentos';
    protected $fillable = ['titulo', 'descripcion', 'categoria', 'departamento', 'curso', 'url', 'archivo', 'user_id'];

    public function autor()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Actualizacion extends Model
{
    protected $table = 'actualizaciones';
    protected $fillable = ['version', 'titulo', 'descripcion', 'fecha', 'autor_id'];

    protected $casts = [
        'fecha' => 'date',
    ];

    public function autor()
    {
        return $this->belongsTo(User::class, 'autor_id');
    }
}

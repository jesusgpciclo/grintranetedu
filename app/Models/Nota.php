<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Nota extends Model
{
    protected $table = 'notas';
    protected $fillable = ['user_id', 'actividad_id', 'valor', 'observaciones'];

    public function alumno()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function actividad()
    {
        return $this->belongsTo(Actividad::class);
    }
}

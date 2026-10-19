<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HallPassNotice extends Model
{
    use HasFactory;

    protected $table = 'hall_pass_notices';

    protected $fillable = [
        'user_id',
        'message',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Alumno al que aplica el aviso.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Docente o administrador que creó el aviso.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope para avisos activos.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolYear extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function calendars()
    {
        return $this->hasMany(Calendar::class);
    }

    public function groups()
    {
        return $this->hasMany(Group::class);
    }

    public function incidencias()
    {
        return $this->hasMany(Incidencia::class);
    }

    public function inventoryItems()
    {
        return $this->hasMany(InventoryItem::class);
    }
}

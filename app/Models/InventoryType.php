<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryType extends Model
{
    protected $fillable = ['nombre'];

    public function subtypes()
    {
        return $this->hasMany(InventorySubtype::class);
    }
}

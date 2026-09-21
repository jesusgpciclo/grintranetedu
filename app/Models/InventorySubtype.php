<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventorySubtype extends Model
{
    protected $fillable = ['nombre', 'inventory_type_id'];

    public function type()
    {
        return $this->belongsTo(InventoryType::class, 'inventory_type_id');
    }
}

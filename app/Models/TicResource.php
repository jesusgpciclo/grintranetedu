<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicResource extends Model
{
    use HasFactory;

    protected $fillable = ['tic_resource_category_id', 'name', 'description', 'is_active'];

    public function category()
    {
        return $this->belongsTo(TicResourceCategory::class, 'tic_resource_category_id');
    }

    public function bookings()
    {
        return $this->hasMany(TicBooking::class);
    }
}

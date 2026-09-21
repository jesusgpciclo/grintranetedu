<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicBooking extends Model
{
    use HasFactory;

    protected $fillable = [
        'tic_resource_id',
        'user_id',
        'date',
        'time_slot_id',
        'observations',
    ];

    public function resource()
    {
        return $this->belongsTo(TicResource::class, 'tic_resource_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function timeSlot()
    {
        return $this->belongsTo(TimeSlot::class);
    }
}

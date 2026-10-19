<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class HallPassIncompatibilityGroup extends Model
{
    use HasFactory;

    protected $table = 'hall_pass_incompatibility_groups';

    protected $fillable = [
        'name',
        'description',
    ];

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'hall_pass_incompatibility_group_user',
            'group_id',
            'user_id'
        )->withTimestamps();
    }
}

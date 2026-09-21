<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
// use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'last_name',
        'departamento',
        'observaciones',
        'course',
        'group',
        'group_id',
        'email',
        'password',
        'google_id',
        'avatar',
        'titular_user_id',
        'must_change_password',
    ];

    public function groupRel()
    {
        return $this->belongsTo(Group::class, 'group_id');
    }

    public function modulos()
    {
        return $this->belongsToMany(Modulo::class, 'modulo_user', 'user_id', 'modulo_id');
    }

    public function tutoredGroups()
    {
        return $this->hasMany(Group::class, 'tutor_id');
    }

    public function ausencias()
    {
        return $this->hasMany(Ausencia::class, 'user_id');
    }

    public function guardiasCubiertas()
    {
        return $this->hasMany(Ausencia::class, 'guardia_user_id');
    }

    public function ultimaGuardiaCubierta()
    {
        return $this->hasOne(Ausencia::class, 'guardia_user_id')
            ->whereNotNull('guardia_confirmed_at')
            ->latestOfMany('fecha');
    }

    public function schedules()
    {
        return $this->hasMany(UserSchedule::class, 'user_id');
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'must_change_password' => 'boolean',
        ];
    }

    public function hallPasses()
    {
        return $this->hasMany(HallPass::class, 'teacher_id');
    }

    public function hallPassesAsStudent()
    {
        return $this->hasMany(HallPass::class, 'user_id');
    }

    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function receivedMessages()
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }

    public function titular()
    {
        return $this->belongsTo(User::class, 'titular_user_id');
    }

    public function sustitutos()
    {
        return $this->hasMany(User::class, 'titular_user_id');
    }

    public function getAvatarUrlAttribute(): ?string
    {
        if (!$this->avatar) {
            return null;
        }
        if (str_starts_with($this->avatar, 'http://') || str_starts_with($this->avatar, 'https://')) {
            return $this->avatar;
        }
        if (str_starts_with($this->avatar, 'avatars/predefined/')) {
            return asset($this->avatar);
        }
        return asset('storage/' . $this->avatar);
    }
}

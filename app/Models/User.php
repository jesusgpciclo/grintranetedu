<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
// use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Collection;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;
    use HasRoles {
        hasRole as spatieHasRole;
        hasPermissionTo as spatieHasPermissionTo;
        getRoleNames as spatieGetRoleNames;
        hasAllRoles as spatieHasAllRoles;
    }

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
        'active_role',
        'favorite_groups',
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
            'favorite_groups' => 'array',
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

    public function canAccessDashboard(): bool
    {
        return $this->hasRole(['admin', 'dashboard']) || $this->can('dashboard.view');
    }

    public function canSelectSchoolYear(): bool
    {
        return $this->hasRole(['admin', 'curso-activo', 'curso_activo']) || $this->can('school_years.select');
    }

    public function getDefaultHomeRoute(): string
    {
        if ($this->hasRole('conserje') && !$this->can('salidas.create') && !$this->hasRole(['admin', 'jefatura', 'directiva', 'director', 'profesor'])) {
            return 'salidas.monitor';
        }
        return 'salidas.index';
    }

    /**
     * Retorna los roles asignados en base de datos.
     */
    public function getAssignedRoles(): Collection
    {
        return $this->roles()->orderBy('name')->get();
    }

    /**
     * Retorna los roles disponibles para alternar (roles identitarios principales).
     */
    public function getSwitchableRoles(): Collection
    {
        $all = $this->roles()->orderBy('name')->get();
        $identityRoles = $all->reject(fn ($r) => in_array($r->name, ['dashboard', 'curso-activo']));
        return $identityRoles->isNotEmpty() ? $identityRoles : $all;
    }

    /**
     * Determina si el usuario tiene más de un rol asignado en la base de datos.
     */
    public function hasMultipleRoles(): bool
    {
        return $this->getSwitchableRoles()->count() > 1;
    }

    /**
     * Retorna el nombre del rol activo actual.
     * Retorna null si no hay restricción de rol activo (modo todos los roles).
     */
    public function getActiveRoleName(): ?string
    {
        $active = session('active_role');
        if ($active === null) {
            $active = $this->active_role;
        }

        if ($active === null || $active === '' || $active === 'all') {
            return null;
        }

        // Validar que el usuario realmente tiene asignado este rol
        $hasIt = $this->roles()->where('name', $active)->exists();
        return $hasIt ? $active : null;
    }

    /**
     * Override de hasRole para respetar el rol activo seleccionado.
     */
    public function hasRole($roles, ?string $guard = null): bool
    {
        $activeRole = $this->getActiveRoleName();

        if ($activeRole === null) {
            return $this->spatieHasRole($roles, $guard);
        }

        if (is_string($roles) && strpos($roles, '|') !== false) {
            $roles = explode('|', $roles);
        }

        if (is_array($roles)) {
            foreach ($roles as $role) {
                if ($this->hasRole($role, $guard)) {
                    return true;
                }
            }
            return false;
        }

        // Roles auxiliares / modificadores asignados se mantienen accesibles
        if (is_string($roles) && in_array($roles, ['dashboard', 'curso-activo'])) {
            return $this->roles()->where('name', $roles)->exists();
        }

        if (is_string($roles)) {
            return $activeRole === $roles;
        }

        if ($roles instanceof Role) {
            return $activeRole === $roles->name;
        }

        if ($roles instanceof Collection) {
            return $roles->pluck('name')->contains($activeRole);
        }

        return $activeRole === (string) $roles;
    }

    /**
     * Override de hasAnyRole para asegurar consistencia con hasRole y middleware Spatie.
     */
    public function hasAnyRole(...$roles): bool
    {
        return $this->hasRole($roles);
    }

    /**
     * Override de hasAllRoles para respetar el rol activo seleccionado.
     */
    public function hasAllRoles($roles, ?string $guard = null): bool
    {
        $activeRole = $this->getActiveRoleName();
        if ($activeRole === null) {
            return $this->spatieHasAllRoles($roles, $guard);
        }

        if (is_string($roles) && strpos($roles, '|') !== false) {
            $roles = explode('|', $roles);
        }

        if (!is_array($roles) && !$roles instanceof Collection) {
            $roles = [$roles];
        }

        foreach ($roles as $role) {
            if (!$this->hasRole($role, $guard)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Override de hasPermissionTo para respetar los permisos del rol activo seleccionado.
     */
    public function hasPermissionTo($permission, $guardName = null): bool
    {
        $activeRole = $this->getActiveRoleName();

        if ($activeRole === null) {
            return $this->spatieHasPermissionTo($permission, $guardName);
        }

        $role = Role::where('name', $activeRole)->first();
        if (!$role) {
            return false;
        }

        if ($role->name === 'admin') {
            return true;
        }

        if ($role->hasPermissionTo($permission, $guardName)) {
            return true;
        }

        // Permisos otorgados por roles modificadores asignados
        if ($permission === 'school_years.select' && $this->roles()->where('name', 'curso-activo')->exists()) {
            return true;
        }
        if ($permission === 'dashboard.view' && $this->roles()->where('name', 'dashboard')->exists()) {
            return true;
        }

        return false;
    }

    /**
     * Override de getRoleNames para mostrar el rol activo en interfaces como la barra lateral.
     */
    public function getRoleNames(): Collection
    {
        $activeRole = $this->getActiveRoleName();

        if ($activeRole !== null) {
            return collect([$activeRole]);
        }

        return $this->spatieGetRoleNames();
    }
}

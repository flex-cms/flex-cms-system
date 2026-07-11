<?php

namespace Flex\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Model
{
    protected $table = 'users';

    protected ?array $permissionsCache = null;

    protected $fillable = [
        'fullname',
        'email',
        'password',
        'remember_token',
        'is_active',
        'options',
        'last_login'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'options' => AsArrayObject::class,
        'last_login' => 'datetime',
    ];

    protected $hidden = [
        'password',
    ];

    protected function password(): Attribute
    {
        return Attribute::make(
            set: fn(?string $value) => !empty($value) ? password_hash($value, PASSWORD_BCRYPT) : ($this->attributes['password'] ?? null)
        );
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_role');
    }

    public function hasPermission(string $permissionSlug): bool
    {
        if ($this->hasRole('admin')) {
            return true;
        }

        return in_array($permissionSlug, $this->getPermissions(), true);
    }

    public function isActive(): bool
    {
        return (bool) $this->is_active;
    }

    public function hasRole(string $roleSlug): bool
    {
        return $this->roles->contains('slug', $roleSlug);
    }

    public function getPermissions(): array
    {
        if ($this->permissionsCache !== null) {
            return $this->permissionsCache;
        }

        $this->permissionsCache = $this->roles()
            ->with('permissions')
            ->get()
            ->pluck('permissions')
            ->collapse()
            ->pluck('slug')
            ->unique()
            ->toArray();

        return $this->permissionsCache;
    }
}
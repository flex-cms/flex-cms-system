<?php

declare(strict_types=1);

namespace Flex\Features\Authentication\Models;

use Flex\Features\Authentication\Support\AuthenticationTables;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

final class User extends Model
{
    use SoftDeletes;

    protected $fillable = ['fullname', 'email', 'password', 'remember_token', 'is_active', 'is_super_admin', 'options', 'last_login'];
    protected $hidden = ['password', 'remember_token'];
    protected $casts = ['is_active' => 'boolean', 'is_super_admin' => 'boolean', 'options' => AsArrayObject::class, 'last_login' => 'datetime'];

    public function getTable(): string
    {
        return AuthenticationTables::users();
    }

    protected function password(): Attribute
    {
        return Attribute::make(set: static fn (?string $value): ?string => $value ? password_hash($value, PASSWORD_ARGON2ID) : null);
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            Role::class,
            AuthenticationTables::userRole(),
            'user_id',
            'role_id'
        );
    }

    public function isSuperAdministrator(): bool
    {
        return $this->is_active && $this->is_super_admin;
    }

    public function hasPermission(string $permission): bool
    {
        if ($this->isSuperAdministrator()) {
            return true;
        }

        return $this->roles()
            ->where(AuthenticationTables::roles() . '.is_active', true)
            ->whereHas(
                'permissions',
                static fn ($query) => $query
                    ->where(AuthenticationTables::permissions() . '.slug', $permission)
                    ->where(AuthenticationTables::permissions() . '.is_active', true)
            )
            ->exists();
    }
}

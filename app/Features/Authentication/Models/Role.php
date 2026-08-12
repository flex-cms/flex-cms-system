<?php

declare(strict_types=1);

namespace Flex\Features\Authentication\Models;

use Flex\Features\Authentication\Support\AuthenticationTables;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Role extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'slug', 'description', 'priority', 'color', 'is_active', 'is_default', 'options'];
    protected $casts = ['priority' => 'integer', 'is_active' => 'boolean', 'is_default' => 'boolean', 'options' => 'array'];

    public function getTable(): string
    {
        return AuthenticationTables::roles();
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            AuthenticationTables::userRole(),
            'role_id',
            'user_id'
        );
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(
            Permission::class,
            AuthenticationTables::rolePermission(),
            'role_id',
            'permission_id'
        );
    }
}

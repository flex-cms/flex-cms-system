<?php

declare(strict_types=1);

namespace Flex\Features\Authentication\Models;

use Flex\Features\Authentication\Support\AuthenticationTables;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Permission extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'slug', 'module', 'description', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];

    public function getTable(): string
    {
        return AuthenticationTables::permissions();
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            Role::class,
            AuthenticationTables::rolePermission(),
            'permission_id',
            'role_id'
        );
    }
}

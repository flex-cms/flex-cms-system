<?php

declare(strict_types=1);

namespace Flex\Features\Authentication\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Permission extends Model
{
    use SoftDeletes;

    protected $table = 'permissions';
    protected $fillable = ['name', 'slug', 'module', 'description', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];
    public function roles(): BelongsToMany { return $this->belongsToMany(Role::class, 'role_permission'); }
}

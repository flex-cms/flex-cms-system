<?php

declare(strict_types=1);

namespace Flex\Features\Authentication\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Role extends Model
{
    use SoftDeletes;

    protected $table = 'roles';
    protected $fillable = ['name', 'slug', 'description', 'priority', 'color', 'is_active', 'is_default', 'options'];
    protected $casts = ['priority' => 'integer', 'is_active' => 'boolean', 'is_default' => 'boolean', 'options' => 'array'];

    public function users(): BelongsToMany { return $this->belongsToMany(User::class, 'user_role'); }
    public function permissions(): BelongsToMany { return $this->belongsToMany(Permission::class, 'role_permission'); }
}

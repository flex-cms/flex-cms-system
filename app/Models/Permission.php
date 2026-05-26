<?php

namespace Flex\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $table = 'permissions';
    
    protected $fillable = [
        'slug',
        'name',
        'module',
        'description'
    ];
    
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_permission', 'permission_id', 'role_id');
    }
}
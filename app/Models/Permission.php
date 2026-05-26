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
}
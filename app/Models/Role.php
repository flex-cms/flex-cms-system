<?php

namespace Flex\Models;

use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Role extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 
        'slug', 
        'description', 
        'priority', 
        'color', 
        'is_active', 
        'is_default', 
        'options'
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'is_default' => 'boolean',
        'priority'   => 'integer',
        'options'    => AsArrayObject::class,
    ];

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_permission');
    }

    public function hasTimeAccess(): bool
    {
        if (!isset($this->options['start_time']) || !isset($this->options['end_time'])) {
            return true;
        }

        $now = date('H:i');
        return $now >= $this->options['start_time'] && $now <= $this->options['end_time'];
    }
}

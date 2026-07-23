<?php

namespace Flex\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Menu extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 
        'slug', 
        'location', 
        'options', 
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'options' => 'array',
        'deleted_at' => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(MenuItem::class)->orderBy('order');
    }

    public function rootItems()
    {
        return $this->hasMany(MenuItem::class)
                    ->whereNull('parent_id')
                    ->orderBy('order');
    }
}
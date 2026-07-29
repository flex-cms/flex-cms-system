<?php

namespace Flex\Models;

use Illuminate\Database\Eloquent\Model;

class Plugin extends Model
{
    protected $table = 'plugins';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'author',
        'author_url',
        'requires',
        'is_active',
        'is_installed',
        'version'
    ];

    protected $casts = [
        'is_installed' => 'boolean',
        'is_active' => 'boolean',
        'author_url' => 'string',
        'requires' => 'array',
    ];

    public static function isActive(string $slug): bool
    {
        return self::where('slug', $slug)->where('is_active', true)->exists();
    }
}
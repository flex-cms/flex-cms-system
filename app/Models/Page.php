<?php

namespace Flex\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;

class Page extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'is_active',
        'created_at',
        'options'
    ];

    protected $casts = [
        'options' => AsArrayObject::class,
    ];

    public function hasImage(): bool
    {
        return isset($this->options['featured_image']);
    }
}
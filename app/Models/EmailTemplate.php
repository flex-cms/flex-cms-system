<?php

namespace Flex\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmailTemplate extends Model
{
    use SoftDeletes;

    protected $table = 'email_templates';

    protected $fillable = [
        'name',
        'slug',
        'category',
        'is_active',
        'subject',
        'body',
        'variables'
    ];

    protected $casts = [
        'variables' => 'array'
    ];
}
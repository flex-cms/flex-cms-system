<?php

namespace Flex\Models;

use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    protected $table = 'email_templates';

    protected $fillable = [
        'name',
        'slug',
        'category',
        'subject',
        'body',
        'variables'
    ];

    protected $casts = [
        'variables' => 'array'
    ];
}

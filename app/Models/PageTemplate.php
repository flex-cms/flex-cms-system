<?php

namespace Flex\Models;

use Illuminate\Database\Eloquent\Model;

class PageTemplate extends Model
{
    protected $table = 'page_templates';
    protected $fillable = ['name', 'slug', 'content', 'is_active'];
}

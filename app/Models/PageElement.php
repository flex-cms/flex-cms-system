<?php

namespace Flex\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PageElement extends Model
{
    protected $fillable = [
        'page_id',
        'parent_id',
        'element_type',
        'position',
        'settings',
    ];

    protected $casts = [
        'page_id' => 'integer',
        'parent_id' => 'integer',
        'position' => 'integer',
        'settings' => AsArrayObject::class,
    ];

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class, 'page_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(PageElement::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(PageElement::class, 'parent_id')
            ->orderBy('position');
    }
}
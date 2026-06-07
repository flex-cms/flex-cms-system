<?php

namespace Flex\Core\PageTemplates\Shared;

use Flex\Core\PageTemplates\Interfaces\PageTemplateInterface;

abstract class BasePageTemplate implements PageTemplateInterface
{
    protected int $pageId;
    protected object $page;

    public function __construct(int $pageId, object $page)
    {
        $this->pageId = $pageId;
        $this->page = $page;
    }

    public static function key(): string
    {
        $class = substr(strrchr(static::class, '\\'), 1);
        return strtolower(str_replace('Template', '', $class));
    }
}

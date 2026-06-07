<?php

namespace Flex\Core\PageTemplates\Attributes;

#[\Attribute(\Attribute::TARGET_CLASS)]
class PageTemplate
{
    public function __construct(
        public string $name,
        public string $key
    ) {}
}
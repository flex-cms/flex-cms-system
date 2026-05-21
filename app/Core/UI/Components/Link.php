<?php

namespace Flex\Core\UI\Components;

class Link
{
    protected string $href;
    protected string $text;

    public function __construct(string $href, string $text)
    {
        $this->href = $href;
        $this->text = $text;
    }

    public static function make(string $href, string $text): self
    {
        return new self($href, $text);
    }

    public function render(): string
    {
        return '<a href="' . $this->href . '"
            class="font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400 transition-colors">
            ' . $this->text . '
        </a>';
    }

    public function __toString(): string
    {
        return $this->render();
    }
}

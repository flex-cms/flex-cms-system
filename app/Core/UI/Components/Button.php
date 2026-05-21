<?php

namespace Flex\Core\UI\Components;

class Button
{
    protected string $text;
    protected string $type;

    public function __construct(string $text, string $type = 'submit')
    {
        $this->text = $text;
        $this->type = $type;
    }

    public static function make(string $text): self
    {
        return new self($text);
    }

    public function type(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function render(): string
    {
        return '<button type="' . $this->type . '"
            class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800 transition-all shadow-md shadow-indigo-500/10 hover:shadow-indigo-500/20">
            ' . $this->text . '
        </button>';
    }

    public function __toString(): string
    {
        return $this->render();
    }
}

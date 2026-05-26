<?php

namespace Flex\Core\UI\Components;

class Button
{
    protected string $text;
    protected string $type;
    protected string $attributes = '';
    protected string $customClasses = '';
    protected string $icon = '';
    protected array $watchConfig = [];

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

    public function icon(string $icon): self
    {
        $this->icon = $icon;
        return $this;
    }

    public function attr(string $attributes): self
    {
        $this->attributes .= ' ' . $attributes;
        return $this;
    }

    public function classes(string $classes): self
    {
        $this->customClasses = $classes;
        return $this;
    }

    public function watch(string $variable, string $condition, string $activeIcon, string $activeText): self
    {
        $this->watchConfig = [
            'type' => 'value',
            'variable' => $variable,
            'condition' => $condition,
            'activeIcon' => $activeIcon,
            'activeText' => $activeText
        ];
        return $this;
    }

    public function toggle(string $variable, string $activeIcon, string $activeText): self
    {
        $this->watchConfig = [
            'type' => 'toggle',
            'variable' => $variable,
            'activeIcon' => $activeIcon,
            'activeText' => $activeText
        ];
        return $this;
    }

    public function render(): string
    {
        $classes = $this->customClasses ?: "group relative w-full flex justify-center py-3 px-4 border border-transparent font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800 transition-all";

        if (!empty($this->watchConfig)) {
            $defaultIcon = $this->icon;
            $defaultText = $this->text;
            $var = $this->watchConfig['variable'];
            $actIcon = $this->watchConfig['activeIcon'];
            $actText = $this->watchConfig['activeText'];

            if ($this->watchConfig['type'] === 'value') {
                $cond = $this->watchConfig['condition'];
                $jsCondition = "v === '{$cond}'";
            } else {
                $jsCondition = "v";
            }

            $this->attributes .= ' x-init="$watch(\'' . $var . '\', v => {
                let isCurrent = ' . $jsCondition . ';
                $el.querySelector(\'i\').className = isCurrent ? \'' . $actIcon . ' mr-2\' : \'' . $defaultIcon . ' mr-2\';
                $el.querySelector(\'span\').innerText = isCurrent ? \'' . $actText . '\' : \'' . $defaultText . '\';
            })"';
        }

        $iconHtml = '';
        if ($this->icon) {
            $iconHtml = '<i class="' . $this->icon . ' mr-2"></i>';
        }

        return '<button type="' . $this->type . '" class="' . $classes . '"' . $this->attributes . '>
            ' . $iconHtml . '<span>' . $this->text . '</span>
        </button>';
    }

    public function __toString(): string
    {
        return $this->render();
    }
}

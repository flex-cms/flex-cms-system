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
        $classes = $this->customClasses ?: "inline-flex items-center px-4 py-2 bg-white hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 font-medium rounded-md border border-slate-200 dark:border-slate-700 transition-all outline-none focus:ring-2 focus:ring-slate-400";

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

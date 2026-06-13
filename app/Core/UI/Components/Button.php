<?php

namespace Flex\Core\UI\Components;

class Button
{
    protected string $text;
    protected string $type;
    protected string $attributes = '';
    protected string $customClasses = '';
    protected string $addedClasses = '';
    protected string $icon = '';
    protected string $size = 'md';
    protected string $fontSize = '';
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

    public function addClasses(string $classes): self
    {
        if (!empty($this->addedClasses)) {
            $this->addedClasses .= ' ';
        }
        $this->addedClasses .= $classes;
        return $this;
    }

    public function variant(string $variant): self
    {
        switch ($variant) {
            case 'secondary':
            case 'light':
                $this->customClasses = "bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-600 font-medium text-slate-700 dark:text-slate-200 transition-all inline-flex items-center justify-center gap-2";
                break;
            default:
                $this->customClasses = "group relative inline-flex justify-center border border-transparent font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800 transition-all items-center gap-2";
                break;
        }
        return $this;
    }

    public function size(string $size): self
    {
        $this->size = $size;
        return $this;
    }

    public function fontSize(string $size): self
    {
        $this->fontSize = $size;
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
        $baseClasses = $this->customClasses ?: "group relative inline-flex justify-center border border-transparent font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800 transition-all items-center gap-2";

        $sizeClasses = '';
        switch ($this->size) {
            case 'xs':
                $sizeClasses = ' py-1.5 px-2.5 text-xs rounded';
                break;
            case 'sm':
                $sizeClasses = ' py-2 px-3 text-xs rounded-md';
                break;
            case 'lg':
                $sizeClasses = ' py-3.5 px-6 text-base rounded-lg';
                break;
            case 'xl':
                $sizeClasses = ' py-4 px-8 text-lg rounded-xl';
                break;
            case 'md':
            default:
                $sizeClasses = ' py-2.5 px-4 text-sm rounded-md h-10';
                break;
        }

        $classes = trim($baseClasses . $sizeClasses);
        if (!empty($this->addedClasses)) {
            $classes .= ' ' . $this->addedClasses;
        }

        if (!empty($this->watchConfig)) {
            $var = $this->watchConfig['variable'];
            $actIcon = $this->watchConfig['activeIcon'];
            $actText = $this->watchConfig['activeText'];
            $jsCondition = ($this->watchConfig['type'] === 'value') ? "v === '{$this->watchConfig['condition']}'" : "v";

            $this->attributes .= ' x-init="$watch(\'' . $var . '\', v => {
                let isCurrent = ' . $jsCondition . ';
                let iconEl = $el.querySelector(\'i\');
                if (iconEl) {
                    iconEl.className = isCurrent ? \'' . $actIcon . ' mr-2\' : \'' . $this->icon . ' mr-2\';
                }
                $el.querySelector(\'span\').innerText = isCurrent ? \'' . $actText . '\' : \'' . $this->text . '\';
            })"';
        }

        $iconHtml = $this->icon ? '<i class="' . $this->icon . ' mr-2"></i>' : '';
        $inlineStyle = $this->fontSize ? ' style="font-size: ' . $this->fontSize . ';"' : '';

        return '<button type="' . $this->type . '" class="' . trim($classes) . '"' . $this->attributes . $inlineStyle . '>
            ' . $iconHtml . '<span>' . $this->text . '</span>
        </button>';
    }

    public function loading(string $variable = 'isUpdating', string $loadingText = 'Зареждане...'): self
    {
        return $this->toggle($variable, 'fas fa-spinner fa-spin', $loadingText);
    }

    public function __toString(): string
    {
        return $this->render();
    }
}
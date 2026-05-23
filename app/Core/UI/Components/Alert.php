<?php

namespace Flex\Core\UI\Components;

class Alert
{
    protected ?string $message;
    protected string $type = 'error'; // Статус по подразбиране

    // Карта с Tailwind класове за различните статуси
    protected array $styles = [
        'error' => [
            'bg' => 'bg-red-50 dark:bg-red-900/30',
            'border' => 'border-red-400',
            'text' => 'text-red-700 dark:text-red-400'
        ],
        'success' => [
            'bg' => 'bg-emerald-50 dark:bg-emerald-900/30',
            'border' => 'border-emerald-400',
            'text' => 'text-emerald-700 dark:text-emerald-400'
        ],
        'info' => [
            'bg' => 'bg-blue-50 dark:bg-blue-900/30',
            'border' => 'border-blue-400',
            'text' => 'text-blue-700 dark:text-blue-400'
        ],
        'warning' => [
            'bg' => 'bg-amber-50 dark:bg-amber-900/30',
            'border' => 'border-amber-400',
            'text' => 'text-amber-700 dark:text-amber-400'
        ]
    ];

    public function __construct(?string $message = null)
    {
        $this->message = $message;
    }

    public static function make(?string $message = null): self
    {
        return new self($message);
    }

    /**
     * Превключва алерта в режим "Грешка"
     */
    public function error(): self
    {
        $this->type = 'error';
        return $this;
    }

    /**
     * Превключва алерта в режим "Успех"
     */
    public function success(): self
    {
        $this->type = 'success';
        return $this;
    }

    /**
     * Превключва алерта в режим "Информация"
     */
    public function info(): self
    {
        $this->type = 'info';
        return $this;
    }

    /**
     * Превключва алерта в режим "Предупреждение"
     */
    public function warning(): self
    {
        $this->type = 'warning';
        return $this;
    }

    /**
     * Динамично рендериране спрямо избрания статус
     */
    public function render(): string
    {
        if (empty($this->message)) {
            return '';
        }

        // Взимаме стиловете за текущия тип (ако типът е невалиден, дефолтваме към error)
        $style = $this->styles[$this->type] ?? $this->styles['error'];

        return '<div class="' . $style['bg'] . ' border-l-4 ' . $style['border'] . ' p-4 mb-4 rounded-r-lg shadow-sm">
            <div class="flex">
                <div class="text-sm ' . $style['text'] . ' font-medium">'
            . htmlspecialchars($this->message) .
            '</div>
            </div>
        </div>';
    }

    public function __toString(): string
    {
        return $this->render();
    }
}

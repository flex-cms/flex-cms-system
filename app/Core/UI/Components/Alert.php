<?php

namespace Flex\Core\UI\Components;

class Alert
{
    protected ?string $message;

    public function __construct(?string $message = null)
    {
        $this->message = $message;
    }

    public static function make(?string $message = null): self
    {
        return new self($message);
    }

    public function render(): string
    {
        if (empty($this->message)) {
            return '';
        }

        return '<div class="bg-red-50 dark:bg-red-900/30 border-l-4 border-red-400 p-4 mb-4 rounded-r-lg">
            <div class="flex">
                <div class="text-sm text-red-700 dark:text-red-400 font-medium">
                    ' . htmlspecialchars($this->message) . '
                </div>
            </div>
        </div>';
    }

    public function __toString(): string
    {
        return $this->render();
    }
}

<?php

namespace Flex\Core\UI\Components;

class InputField
{
    protected string $id;
    protected string $name;
    protected string $type;
    protected string $label;
    protected string $placeholder;
    protected string $value;
    protected bool $required;

    public function __construct(string $name, string $label, string $type = 'text', string $value = '', string $placeholder = '', bool $required = false)
    {
        $this->name = $name;
        $this->id = $name;
        $this->label = $label;
        $this->type = $type;
        $this->value = $value;
        $this->placeholder = $placeholder;
        $this->required = $required;
    }

    public static function make(string $name, string $label): self
    {
        return new self($name, $label);
    }

    public function type(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function value(string $value): self
    {
        $this->value = $value;
        return $this;
    }

    public function placeholder(string $placeholder): self
    {
        $this->placeholder = $placeholder;
        return $this;
    }

    public function required(bool $required = true): self
    {
        $this->required = $required;
        return $this;
    }

    public function render(): string
    {
        $escapedValue = htmlspecialchars($this->value);
        $escapedPlaceholder = htmlspecialchars($this->placeholder);
        $requiredAttr = $this->required ? 'required' : '';
        $asterisk = $this->required ? ' <span class="text-red-500">*</span>' : '';

        return '<div>
                    <label for="' . $this->id . '" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        ' . $this->label . $asterisk . '
                    </label>
                    <input id="' . $this->id . '" name="' . $this->name . '" type="' . $this->type . '" ' . $requiredAttr . '
                        value="' . $escapedValue . '"
                        class="appearance-none block w-full px-3 py-3 border border-gray-300 dark:border-gray-600 rounded-lg placeholder-gray-400 dark:placeholder-gray-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent sm:text-sm transition-all"
                        placeholder="' . $escapedPlaceholder . '">
                </div>';
    }

    public function __toString(): string
    {
        return $this->render();
    }
}
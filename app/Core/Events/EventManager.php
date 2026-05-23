<?php

namespace Flex\Core\Events;

/**
 * EventManager - Нервната система на Flex CMS.
 * Управлява "Actions" (за изпълнение на код) и "Filters" (за промяна на данни).
 */
class EventManager
{
    private static $instance = null;
    protected $actions = [];
    protected $filters = [];

    private function __construct()
    {
        self::$instance = $this;
    }

    private function __clone() {}

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function listen(string $hook, callable $callback, int $priority = 10): void
    {
        $this->actions[$hook][$priority][] = $callback;
    }

    public function trigger(string $hook, ...$args): void
    {
        if (!isset($this->actions[$hook]))
            return;

        ksort($this->actions[$hook]);

        foreach ($this->actions[$hook] as $priority => $callbacks) {
            foreach ($callbacks as $callback) {
                call_user_func_array($callback, $args);
            }
        }
    }

    public function addFilter(string $hook, callable $callback, int $priority = 10): void
    {
        $this->filters[$hook][$priority][] = $callback;
    }

    public function applyFilters(string $hook, $value, ...$args)
    {
        if (!isset($this->filters[$hook]))
            return $value;

        ksort($this->filters[$hook]);

        foreach ($this->filters[$hook] as $priority => $callbacks) {
            foreach ($callbacks as $callback) {
                $value = call_user_func($callback, $value, ...$args);
            }
        }

        return $value;
    }
}

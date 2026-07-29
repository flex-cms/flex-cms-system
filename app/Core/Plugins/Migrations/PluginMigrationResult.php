<?php

namespace Flex\Core\Plugins\Migrations;

final readonly class PluginMigrationResult
{
    public function __construct(
        public string $pluginSlug,
        public int $batch,
        public array $migrations,
        public bool $successful = true,
        public ?string $message = null,
    ) {
    }

    public function count(): int
    {
        return count($this->migrations);
    }

    public function toArray(): array
    {
        return [
            'plugin_slug' => $this->pluginSlug,
            'batch' => $this->batch,
            'migrations' => $this->migrations,
            'count' => $this->count(),
            'successful' => $this->successful,
            'message' => $this->message,
        ];
    }
}
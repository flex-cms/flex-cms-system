<?php

namespace Flex\Core\Admin;

final class AdminMenuManager
{
    /**
     * @var array<string, array>
     */
    private array $items = [];

    public function add(
        string $key,
        array $item,
        ?string $pluginSlug = null
    ): void {
        $item = $this->normalizeItem($item);

        $item['key'] = $key;
        $item['plugin'] = $pluginSlug;

        $this->items[$key] = $item;
    }

    public function remove(string $key): void
    {
        unset($this->items[$key]);
    }

    public function removeByPlugin(string $pluginSlug): void
    {
        $this->items = array_filter(
            $this->items,
            static fn(array $item): bool =>
            ($item['plugin'] ?? null) !== $pluginSlug
        );
    }

    public function has(string $key): bool
    {
        return isset($this->items[$key]);
    }

    public function get(string $key): ?array
    {
        return $this->items[$key] ?? null;
    }

    public function all(): array
    {
        $items = array_values($this->items);

        usort(
            $items,
            static fn(array $first, array $second): int =>
            ($first['position'] ?? 100)
            <=>
            ($second['position'] ?? 100)
        );

        return $items;
    }

    private function normalizeItem(array $item): array
    {
        $children = $item['children'] ?? [];

        if (!is_array($children)) {
            $children = [];
        }

        $children = array_map(
            fn(array $child): array =>
            $this->normalizeItem($child),
            array_filter($children, 'is_array')
        );

        usort(
            $children,
            static fn(array $first, array $second): int =>
            ($first['position'] ?? 100)
            <=>
            ($second['position'] ?? 100)
        );

        return [
            'title' => trim((string) ($item['title'] ?? '')),
            'url' => $item['url'] ?? null,
            'icon' => $item['icon'] ?? null,
            'position' => (int) ($item['position'] ?? 100),
            'permission' => $item['permission'] ?? null,
            'children' => $children,
        ];
    }
}

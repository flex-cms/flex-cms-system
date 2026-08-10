<?php

declare(strict_types=1);

namespace Flex\Features\AdminUI\Navigation;

use InvalidArgumentException;
use LogicException;

final class SidebarDefinition
{
    private SidebarPosition $sidebarPosition;

    private int $sidebarPriority = 100;

    private bool $collapsible = true;

    /**
     * @var array<string, NavigationItem>
     */
    private array $items = [];

    private function __construct(
        private readonly string $sidebarId,
        private string $sidebarLabel
    ) {
        $this->sidebarPosition =
            SidebarPosition::Left;
    }

    public static function make(
        string $id,
        string $label
    ): self {
        $id = trim($id);
        $label = trim($label);

        if (
            !preg_match(
                '/^[A-Za-z][A-Za-z0-9._-]*$/',
                $id
            )
        ) {
            throw new InvalidArgumentException(
                sprintf(
                    'Invalid sidebar id [%s].',
                    $id
                )
            );
        }

        if ($label === '') {
            throw new InvalidArgumentException(
                'A sidebar label cannot be empty.'
            );
        }

        return new self($id, $label);
    }

    public function position(
        SidebarPosition|string $position
    ): self {
        $this->sidebarPosition =
            SidebarPosition::resolve($position);

        return $this;
    }

    public function priority(int $priority): self
    {
        $this->sidebarPriority = $priority;

        return $this;
    }

    public function collapsible(
        bool $collapsible = true
    ): self {
        $this->collapsible = $collapsible;

        return $this;
    }

    public function add(
        NavigationItem $item
    ): self {
        $this->assertUniqueItemId(
            $item->id()
        );

        $this->items[$item->id()] = $item;

        return $this;
    }

    public function addTo(
        string $parentId,
        NavigationItem $item
    ): self {
        $parentId = trim($parentId);

        if ($parentId === '') {
            throw new InvalidArgumentException(
                'A parent navigation id cannot be empty.'
            );
        }

        $this->assertUniqueItemId(
            $item->id()
        );

        foreach ($this->items as $rootId => $root) {
            [$updated, $found] =
                $this->appendToItem(
                    $root,
                    $parentId,
                    $item
                );

            if (!$found) {
                continue;
            }

            $this->items[$rootId] = $updated;

            return $this;
        }

        throw new LogicException(
            sprintf(
                'Navigation parent [%s] was not found in sidebar [%s].',
                $parentId,
                $this->sidebarId
            )
        );
    }

    public function remove(
        string $itemId
    ): self {
        if (isset($this->items[$itemId])) {
            unset($this->items[$itemId]);

            return $this;
        }

        foreach ($this->items as $rootId => $root) {
            [$updated, $removed] =
                $this->removeFromItem(
                    $root,
                    $itemId
                );

            if (!$removed) {
                continue;
            }

            $this->items[$rootId] = $updated;

            return $this;
        }

        return $this;
    }

    public function has(string $itemId): bool
    {
        return $this->find($itemId) !== null;
    }

    public function find(
        string $itemId
    ): ?NavigationItem {
        foreach ($this->items as $item) {
            $found = $this->findInItem(
                $item,
                $itemId
            );

            if ($found !== null) {
                return $found;
            }
        }

        return null;
    }

    public function id(): string
    {
        return $this->sidebarId;
    }

    public function label(): string
    {
        return $this->sidebarLabel;
    }

    public function positionValue(): SidebarPosition
    {
        return $this->sidebarPosition;
    }

    public function priorityValue(): int
    {
        return $this->sidebarPriority;
    }

    public function isCollapsible(): bool
    {
        return $this->collapsible;
    }

    /**
     * @return list<NavigationItem>
     */
    public function navigationItems(): array
    {
        $items = array_values($this->items);

        usort(
            $items,
            static fn(
            NavigationItem $left,
            NavigationItem $right
        ): int => [
                $left->priorityValue(),
                strtolower($left->label()),
            ] <=> [
                $right->priorityValue(),
                strtolower($right->label()),
            ]
        );

        return $items;
    }

    /**
     * @return array{
     *     id: string,
     *     label: string,
     *     position: string,
     *     priority: int,
     *     collapsible: bool,
     *     items: list<array<string, mixed>>
     * }
     */
    public function toArray(
        mixed $context = null
    ): array {
        $items = [];

        foreach (
            $this->navigationItems()
            as $item
        ) {
            if (!$item->isVisible($context)) {
                continue;
            }

            $items[] = $item->toArray(
                $context
            );
        }

        return [
            'id' => $this->sidebarId,
            'label' => $this->sidebarLabel,
            'position' =>
                $this->sidebarPosition->value,
            'priority' =>
                $this->sidebarPriority,
            'collapsible' =>
                $this->collapsible,
            'items' => $items,
        ];
    }

    private function assertUniqueItemId(
        string $itemId
    ): void {
        if (!$this->has($itemId)) {
            return;
        }

        throw new LogicException(
            sprintf(
                'Navigation item [%s] is already registered in sidebar [%s].',
                $itemId,
                $this->sidebarId
            )
        );
    }

    private function findInItem(
        NavigationItem $item,
        string $itemId
    ): ?NavigationItem {
        if ($item->id() === $itemId) {
            return $item;
        }

        foreach (
            $item->childItems()
            as $child
        ) {
            $found = $this->findInItem(
                $child,
                $itemId
            );

            if ($found !== null) {
                return $found;
            }
        }

        return null;
    }

    /**
     * @return array{0: NavigationItem, 1: bool}
     */
    private function appendToItem(
        NavigationItem $current,
        string $parentId,
        NavigationItem $newItem
    ): array {
        if ($current->id() === $parentId) {
            return [
                $current->addChild($newItem),
                true,
            ];
        }

        $children = $current->childItems();

        foreach ($children as $index => $child) {
            [$updated, $found] =
                $this->appendToItem(
                    $child,
                    $parentId,
                    $newItem
                );

            if (!$found) {
                continue;
            }

            $children[$index] = $updated;

            return [
                $current->children($children),
                true,
            ];
        }

        return [$current, false];
    }

    /**
     * @return array{0: NavigationItem, 1: bool}
     */
    private function removeFromItem(
        NavigationItem $current,
        string $itemId
    ): array {
        $children = $current->childItems();

        foreach ($children as $index => $child) {
            if ($child->id() === $itemId) {
                unset($children[$index]);

                return [
                    $current->children(
                        array_values($children)
                    ),
                    true,
                ];
            }

            [$updated, $removed] =
                $this->removeFromItem(
                    $child,
                    $itemId
                );

            if (!$removed) {
                continue;
            }

            $children[$index] = $updated;

            return [
                $current->children($children),
                true,
            ];
        }

        return [$current, false];
    }
}

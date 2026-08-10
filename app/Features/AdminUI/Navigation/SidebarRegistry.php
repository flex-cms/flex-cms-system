<?php

declare(strict_types=1);

namespace Flex\Features\AdminUI\Navigation;

use Closure;
use InvalidArgumentException;
use LogicException;

final class SidebarRegistry
{
    public const DEFAULT_SIDEBAR =
        'admin-primary';

    /**
     * @var array<string, SidebarDefinition>
     */
    private array $sidebars = [];

    public function create(
        string $id,
        string $label,
        SidebarPosition|string $position =
            SidebarPosition::Left
    ): SidebarDefinition {
        if ($this->has($id)) {
            throw new LogicException(
                sprintf(
                    'Sidebar [%s] is already registered.',
                    $id
                )
            );
        }

        $sidebar = SidebarDefinition::make(
            $id,
            $label
        )->position($position);

        $this->sidebars[$sidebar->id()] =
            $sidebar;

        return $sidebar;
    }

    public function register(
        SidebarDefinition $sidebar
    ): self {
        if ($this->has($sidebar->id())) {
            throw new LogicException(
                sprintf(
                    'Sidebar [%s] is already registered.',
                    $sidebar->id()
                )
            );
        }

        $this->sidebars[$sidebar->id()] =
            $sidebar;

        return $this;
    }

    public function sidebar(
        string $id = self::DEFAULT_SIDEBAR
    ): SidebarDefinition {
        $sidebar = $this->find($id);

        if ($sidebar !== null) {
            return $sidebar;
        }

        throw new LogicException(
            sprintf(
                'Sidebar [%s] is not registered.',
                $id
            )
        );
    }

    public function find(
        string $id
    ): ?SidebarDefinition {
        return $this->sidebars[$id] ?? null;
    }

    public function has(string $id): bool
    {
        return isset($this->sidebars[$id]);
    }

    public function remove(string $id): self
    {
        unset($this->sidebars[$id]);

        return $this;
    }

    public function add(
        NavigationItem $item,
        string $sidebarId =
            self::DEFAULT_SIDEBAR
    ): self {
        $this->sidebar($sidebarId)
            ->add($item);

        return $this;
    }

    public function addTo(
        string $parentId,
        NavigationItem $item,
        string $sidebarId =
            self::DEFAULT_SIDEBAR
    ): self {
        $this->sidebar($sidebarId)
            ->addTo(
                $parentId,
                $item
            );

        return $this;
    }

    public function configure(
        string $sidebarId,
        callable $configuration
    ): self {
        $configuration = Closure::fromCallable(
            $configuration
        );

        $configuration(
            $this->sidebar($sidebarId),
            $this
        );

        return $this;
    }

    /**
     * @return list<SidebarDefinition>
     */
    public function all(): array
    {
        $sidebars = array_values(
            $this->sidebars
        );

        usort(
            $sidebars,
            static fn (
                SidebarDefinition $left,
                SidebarDefinition $right
            ): int => [
                $left->priorityValue(),
                strtolower($left->label()),
            ] <=> [
                $right->priorityValue(),
                strtolower($right->label()),
            ]
        );

        return $sidebars;
    }

    /**
     * @return list<SidebarDefinition>
     */
    public function positioned(
        SidebarPosition|string $position
    ): array {
        $position = SidebarPosition::resolve(
            $position
        );

        return array_values(
            array_filter(
                $this->all(),
                static fn (
                    SidebarDefinition $sidebar
                ): bool =>
                    $sidebar->positionValue()
                    === $position
            )
        );
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function toArray(
        mixed $context = null
    ): array {
        return array_map(
            static fn (
                SidebarDefinition $sidebar
            ): array =>
                $sidebar->toArray($context),
            $this->all()
        );
    }

    /**
     * Връща sidebar за layout, когато очакваме
     * точно една основна лента.
     */
    public function primary(
        string $id = self::DEFAULT_SIDEBAR
    ): array {
        return $this->sidebar($id)->toArray();
    }

    public function assertValidSidebarId(
        string $id
    ): void {
        if (trim($id) === '') {
            throw new InvalidArgumentException(
                'A sidebar id cannot be empty.'
            );
        }
    }
}

<?php

declare(strict_types=1);

namespace Flex\Features\AdminUI\Navigation;

use Closure;
use InvalidArgumentException;
use JsonSerializable;

final class NavigationItem implements JsonSerializable
{
    private string $icon = 'fa-solid fa-circle';

    private int $priority = 100;

    private string|int|null $badge = null;

    private bool $turboEnabled = false;

    private bool $exactMatch = false;

    private string $target = '_self';

    /**
     * @var list<string>
     */
    private array $activePatterns = [];

    /**
     * @var list<NavigationItem>
     */
    private array $children = [];

    private ?Closure $visibilityResolver = null;

    private function __construct(
        private readonly string $id,
        private readonly string $label,
        private readonly ?string $url = null
    ) {
    }

    public static function make(
        string $id,
        string $label,
        ?string $url = null
    ): self {
        $id = trim($id);
        $label = trim($label);
        $url = $url !== null
            ? trim($url)
            : null;

        if (
            !preg_match(
                '/^[A-Za-z][A-Za-z0-9._-]*$/',
                $id
            )
        ) {
            throw new InvalidArgumentException(
                sprintf(
                    'Invalid navigation item id [%s].',
                    $id
                )
            );
        }

        if ($label === '') {
            throw new InvalidArgumentException(
                'A navigation item label cannot be empty.'
            );
        }

        if ($url === '') {
            $url = null;
        }

        return new self(
            id: $id,
            label: $label,
            url: $url
        );
    }

    public function icon(string $icon): self
    {
        $icon = trim($icon);

        if ($icon === '') {
            throw new InvalidArgumentException(
                'A navigation icon cannot be empty.'
            );
        }

        $clone = clone $this;
        $clone->icon = $icon;

        return $clone;
    }

    public function priority(int $priority): self
    {
        $clone = clone $this;
        $clone->priority = $priority;

        return $clone;
    }

    public function badge(
        string|int|null $badge
    ): self {
        $clone = clone $this;
        $clone->badge = $badge;

        return $clone;
    }

    public function turbo(
        bool $enabled = true
    ): self {
        $clone = clone $this;
        $clone->turboEnabled = $enabled;

        return $clone;
    }

    public function exact(
        bool $exact = true
    ): self {
        $clone = clone $this;
        $clone->exactMatch = $exact;

        return $clone;
    }

    public function target(string $target): self
    {
        $target = trim($target);

        if (
            !in_array(
                $target,
                ['_self', '_blank', '_parent', '_top'],
                true
            )
        ) {
            throw new InvalidArgumentException(
                sprintf(
                    'Unsupported navigation target [%s].',
                    $target
                )
            );
        }

        $clone = clone $this;
        $clone->target = $target;

        return $clone;
    }

    public function activeWhen(
        string ...$patterns
    ): self {
        $normalized = [];

        foreach ($patterns as $pattern) {
            $pattern = trim($pattern);

            if ($pattern !== '') {
                $normalized[] = $pattern;
            }
        }

        $clone = clone $this;
        $clone->activePatterns = array_values(
            array_unique([
                ...$this->activePatterns,
                ...$normalized,
            ])
        );

        return $clone;
    }

    public function visibleWhen(
        callable $resolver
    ): self {
        $clone = clone $this;
        $clone->visibilityResolver =
            Closure::fromCallable($resolver);

        return $clone;
    }

    /**
     * @param list<NavigationItem> $children
     */
    public function children(array $children): self
    {
        $validated = [];

        foreach ($children as $child) {
            if (!$child instanceof self) {
                throw new InvalidArgumentException(
                    'Navigation children must be NavigationItem objects.'
                );
            }

            $validated[] = $child;
        }

        $clone = clone $this;
        $clone->children = $validated;

        return $clone;
    }

    public function addChild(
        NavigationItem $child
    ): self {
        $clone = clone $this;
        $clone->children = [
            ...$this->children,
            $child,
        ];

        return $clone;
    }

    public function isVisible(
        mixed $context = null
    ): bool {
        if ($this->visibilityResolver === null) {
            return true;
        }

        return (bool) (
            $this->visibilityResolver
        )($context, $this);
    }

    public function id(): string
    {
        return $this->id;
    }

    public function label(): string
    {
        return $this->label;
    }

    public function url(): ?string
    {
        return $this->url;
    }

    public function iconName(): string
    {
        return $this->icon;
    }

    public function priorityValue(): int
    {
        return $this->priority;
    }

    /**
     * @return list<NavigationItem>
     */
    public function childItems(): array
    {
        return $this->children;
    }

    /**
     * @return array{
     *     id: string,
     *     label: string,
     *     url: ?string,
     *     icon: string,
     *     priority: int,
     *     badge: string|int|null,
     *     turbo: bool,
     *     exact: bool,
     *     target: string,
     *     activePatterns: list<string>,
     *     children: list<array<string, mixed>>
     * }
     */
    public function toArray(
        mixed $context = null
    ): array {
        $children = [];

        foreach ($this->sortedChildren() as $child) {
            if (!$child->isVisible($context)) {
                continue;
            }

            $children[] = $child->toArray(
                $context
            );
        }

        return [
            'id' => $this->id,
            'label' => $this->label,
            'url' => $this->url,
            'icon' => $this->icon,
            'priority' => $this->priority,
            'badge' => $this->badge,
            'turbo' => $this->turboEnabled,
            'exact' => $this->exactMatch,
            'target' => $this->target,
            'activePatterns' =>
                $this->activePatterns,
            'children' => $children,
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * @return list<NavigationItem>
     */
    private function sortedChildren(): array
    {
        $children = $this->children;

        usort(
            $children,
            static fn (
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

        return $children;
    }
}

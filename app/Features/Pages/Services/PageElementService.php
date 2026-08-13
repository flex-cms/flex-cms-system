<?php

declare(strict_types=1);

namespace Flex\Features\Pages\Services;

use Flex\Features\Pages\Data\PageElementNode;
use Flex\Features\Pages\Exceptions\InvalidPageElementException;
use Flex\Features\Pages\Models\Page;
use Flex\Features\Pages\Models\PageElement;
use Flex\Features\Pages\Repositories\PageElementRepositoryInterface;
use JsonException;

final readonly class PageElementService
{
    private const MAX_TYPE_LENGTH = 100;

    public function __construct(
        private PageElementRepositoryInterface $elements
    ) {
    }

    /** @return list<PageElementNode> */
    public function tree(Page $page): array
    {
        $elements = $this->elements->allFor($page);
        $byId = [];

        foreach ($elements as $element) {
            $byId[(int) $element->id] = $element;
        }

        $children = [];
        $roots = [];

        foreach ($byId as $element) {
            $parentId = $element->parent_id !== null
                ? (int) $element->parent_id
                : null;

            if ($parentId === null || !isset($byId[$parentId])) {
                $roots[] = $element;
                continue;
            }

            $children[$parentId][] = $element;
        }

        $this->sortElements($roots);

        foreach ($children as &$siblings) {
            $this->sortElements($siblings);
        }

        unset($siblings);

        $nodes = [];
        $visited = [];

        foreach ($roots as $root) {
            $nodes[] = $this->node($root, $children, $visited);
        }

        if (count($visited) !== count($byId)) {
            throw new InvalidPageElementException(
                'A cyclic page element hierarchy cannot be rendered.'
            );
        }

        return $nodes;
    }

    /**
     * @param list<array<string, mixed>> $definitions
     * @param list<string>|null $allowedTypes
     * @return list<PageElementNode>
     */
    public function replace(
        Page $page,
        array $definitions,
        ?array $allowedTypes = null
    ): array {
        if (!array_is_list($definitions)) {
            throw new InvalidPageElementException(
                'Page element definitions must be a list.'
            );
        }

        $allowed = $this->allowedTypes($allowedTypes);
        $usedIds = [];

        $this->elements->transaction(
            function () use (
                $page,
                $definitions,
                $allowed,
                &$usedIds
            ): void {
                $this->syncLevel(
                    $page,
                    $definitions,
                    null,
                    $allowed,
                    $usedIds
                );

                $this->elements->deleteMissing(
                    $page,
                    array_keys($usedIds)
                );
            }
        );

        return $this->tree($page);
    }

    public function remove(Page $page, int $elementId): bool
    {
        if ($elementId < 1) {
            throw new InvalidPageElementException(
                'A page element id must be a positive integer.'
            );
        }

        $element = $this->elements->findForPage($page, $elementId);

        if ($element === null) {
            return false;
        }

        $this->elements->delete($element);

        return true;
    }

    /**
     * @param list<array<string, mixed>> $definitions
     * @param array<string, true>|null $allowedTypes
     * @param array<int, true> $usedIds
     */
    private function syncLevel(
        Page $page,
        array $definitions,
        ?int $parentId,
        ?array $allowedTypes,
        array &$usedIds
    ): void {
        foreach ($definitions as $index => $definition) {
            if (!is_array($definition)) {
                throw new InvalidPageElementException(
                    'Each page element definition must be an array.'
                );
            }

            $id = $this->optionalId($definition['id'] ?? null);

            if ($id !== null && isset($usedIds[$id])) {
                throw new InvalidPageElementException(
                    sprintf('Page element [%d] occurs more than once.', $id)
                );
            }

            $type = $this->type(
                $definition['element_type']
                    ?? $definition['type']
                    ?? null,
                $allowedTypes
            );
            $position = array_key_exists('position', $definition)
                ? $this->position($definition['position'])
                : $index;
            $settings = $this->settings($definition['settings'] ?? []);
            $children = $definition['children'] ?? [];

            if (!is_array($children) || !array_is_list($children)) {
                throw new InvalidPageElementException(
                    'Page element children must be a list.'
                );
            }

            if ($id === null) {
                $element = $this->elements->create($page, [
                    'parent_id' => $parentId,
                    'element_type' => $type,
                    'position' => $position,
                    'settings' => $settings,
                ]);
            } else {
                $element = $this->elements->findForPage($page, $id);

                if ($element === null) {
                    throw new InvalidPageElementException(
                        sprintf(
                            'Page element [%d] does not belong to page [%d].',
                            $id,
                            (int) $page->id
                        )
                    );
                }

                $element = $this->elements->update($element, [
                    'parent_id' => $parentId,
                    'element_type' => $type,
                    'position' => $position,
                    'settings' => $settings,
                ]);
            }

            $elementId = (int) $element->id;
            $usedIds[$elementId] = true;

            $this->syncLevel(
                $page,
                $children,
                $elementId,
                $allowedTypes,
                $usedIds
            );
        }
    }

    /** @return array<string, true>|null */
    private function allowedTypes(?array $allowedTypes): ?array
    {
        if ($allowedTypes === null) {
            return null;
        }

        $allowed = [];

        foreach ($allowedTypes as $type) {
            $allowed[$this->type($type)] = true;
        }

        return $allowed;
    }

    /** @param array<string, true>|null $allowedTypes */
    private function type(
        mixed $value,
        ?array $allowedTypes = null
    ): string {
        if (!is_string($value)) {
            throw new InvalidPageElementException(
                'A page element type must be a string.'
            );
        }

        $type = trim($value);

        if (
            $type === ''
            || strlen($type) > self::MAX_TYPE_LENGTH
            || !preg_match('/^[a-z][a-z0-9_.-]*$/i', $type)
        ) {
            throw new InvalidPageElementException(
                sprintf('Page element type [%s] is invalid.', $type)
            );
        }

        if ($allowedTypes !== null && !isset($allowedTypes[$type])) {
            throw new InvalidPageElementException(
                sprintf('Page element type [%s] is not allowed.', $type)
            );
        }

        return $type;
    }

    /** @return array<string, mixed> */
    private function settings(mixed $value): array
    {
        if (!is_array($value)) {
            throw new InvalidPageElementException(
                'Page element settings must be an array.'
            );
        }

        try {
            json_encode(
                $value,
                JSON_THROW_ON_ERROR
                | JSON_UNESCAPED_UNICODE
                | JSON_UNESCAPED_SLASHES
            );
        } catch (JsonException $exception) {
            throw new InvalidPageElementException(
                'Page element settings cannot be encoded.',
                previous: $exception
            );
        }

        return $value;
    }

    private function optionalId(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        $id = filter_var(
            $value,
            FILTER_VALIDATE_INT,
            FILTER_NULL_ON_FAILURE
        );

        if ($id === null || $id < 1) {
            throw new InvalidPageElementException(
                'A page element id must be a positive integer.'
            );
        }

        return $id;
    }

    private function position(mixed $value): int
    {
        $position = filter_var(
            $value,
            FILTER_VALIDATE_INT,
            FILTER_NULL_ON_FAILURE
        );

        if ($position === null || $position < 0) {
            throw new InvalidPageElementException(
                'Page element position must be a non-negative integer.'
            );
        }

        return $position;
    }

    /** @param list<PageElement> $elements */
    private function sortElements(array &$elements): void
    {
        usort(
            $elements,
            static fn (PageElement $left, PageElement $right): int => [
                (int) $left->position,
                (int) $left->id,
            ] <=> [
                (int) $right->position,
                (int) $right->id,
            ]
        );
    }

    /**
     * @param array<int, list<PageElement>> $children
     * @param array<int, true> $visited
     */
    private function node(
        PageElement $element,
        array $children,
        array &$visited
    ): PageElementNode {
        $id = (int) $element->id;

        if (isset($visited[$id])) {
            throw new InvalidPageElementException(
                'A cyclic page element hierarchy cannot be rendered.'
            );
        }

        $visited[$id] = true;
        $nodes = [];

        foreach ($children[$id] ?? [] as $child) {
            $nodes[] = $this->node($child, $children, $visited);
        }

        return new PageElementNode($element, $nodes);
    }
}

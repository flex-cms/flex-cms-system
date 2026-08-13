<?php

declare(strict_types=1);

namespace Flex\Features\Pages\Services;

use Flex\Features\Pages\Exceptions\InvalidPageOptionException;
use Flex\Features\Pages\Models\Page;
use Flex\Features\Pages\Repositories\PageOptionRepositoryInterface;
use JsonException;

final readonly class PageOptionService
{
    private const MAX_KEY_LENGTH = 255;

    public function __construct(
        private PageOptionRepositoryInterface $options
    ) {
    }

    /** @return array<string, mixed> */
    public function values(Page $page): array
    {
        $values = [];

        foreach ($this->options->allFor($page) as $option) {
            $values[$option->option_key] = $option->decodedValue();
        }

        return $values;
    }

    public function value(
        Page $page,
        string $key,
        mixed $default = null
    ): mixed {
        $key = $this->key($key);
        $option = $this->options->find($page, $key);

        return $option === null
            ? $default
            : $option->decodedValue();
    }

    public function save(Page $page, string $key, mixed $value): void
    {
        $this->options->save(
            $page,
            $this->key($key),
            $this->encode($value)
        );
    }

    /**
     * Saves only the submitted options and preserves all other stored keys.
     *
     * @param array<string, mixed> $values
     * @param list<string>|null $allowedKeys
     */
    public function saveMany(
        Page $page,
        array $values,
        ?array $allowedKeys = null
    ): void {
        $normalized = $this->normalize($values, $allowedKeys);

        if ($normalized === []) {
            return;
        }

        $this->options->transaction(
            function () use ($page, $normalized): void {
                foreach ($normalized as $key => $encodedValue) {
                    $this->options->save($page, $key, $encodedValue);
                }
            }
        );
    }

    /**
     * Replaces the complete option set and removes keys not present in $values.
     *
     * @param array<string, mixed> $values
     * @param list<string>|null $allowedKeys
     */
    public function replace(
        Page $page,
        array $values,
        ?array $allowedKeys = null
    ): void {
        $normalized = $this->normalize($values, $allowedKeys);

        $this->options->transaction(
            function () use ($page, $normalized): void {
                foreach ($normalized as $key => $encodedValue) {
                    $this->options->save($page, $key, $encodedValue);
                }

                $this->options->deleteMissing(
                    $page,
                    array_keys($normalized)
                );
            }
        );
    }

    public function remove(Page $page, string $key): bool
    {
        return $this->options->delete(
            $page,
            $this->key($key)
        ) > 0;
    }

    /**
     * @param array<string, mixed> $values
     * @param list<string>|null $allowedKeys
     * @return array<string, string>
     */
    private function normalize(
        array $values,
        ?array $allowedKeys
    ): array {
        $allowed = null;

        if ($allowedKeys !== null) {
            $allowed = [];

            foreach ($allowedKeys as $allowedKey) {
                $allowed[$this->key($allowedKey)] = true;
            }
        }

        $normalized = [];

        foreach ($values as $key => $value) {
            $key = $this->key((string) $key);

            if ($allowed !== null && !isset($allowed[$key])) {
                throw new InvalidPageOptionException(
                    sprintf('Page option [%s] is not allowed.', $key)
                );
            }

            if (array_key_exists($key, $normalized)) {
                throw new InvalidPageOptionException(
                    sprintf('Page option [%s] occurs more than once.', $key)
                );
            }

            $normalized[$key] = $this->encode($value);
        }

        return $normalized;
    }

    private function key(string $key): string
    {
        $key = trim($key);

        if ($key === '') {
            throw new InvalidPageOptionException(
                'A page option key cannot be empty.'
            );
        }

        if (strlen($key) > self::MAX_KEY_LENGTH) {
            throw new InvalidPageOptionException(
                sprintf(
                    'A page option key cannot exceed %d characters.',
                    self::MAX_KEY_LENGTH
                )
            );
        }

        if (!preg_match('/^[a-z][a-z0-9_.-]*$/i', $key)) {
            throw new InvalidPageOptionException(
                sprintf('Page option key [%s] is invalid.', $key)
            );
        }

        return $key;
    }

    private function encode(mixed $value): string
    {
        try {
            return json_encode(
                $value,
                JSON_THROW_ON_ERROR
                | JSON_UNESCAPED_UNICODE
                | JSON_UNESCAPED_SLASHES
            );
        } catch (JsonException $exception) {
            throw new InvalidPageOptionException(
                'A page option contains a value that cannot be encoded.',
                previous: $exception
            );
        }
    }
}

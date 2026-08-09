<?php

declare(strict_types=1);

namespace Flex\Core\View;

use Flex\Core\View\Exceptions\ViewException;
use Flex\Core\View\Exceptions\ViewNotFoundException;

final readonly class ViewFinder
{
    public function __construct(private string $rootPath)
    {
    }

    public function find(string $view): string
    {
        [$namespace, $path] = $this->parse($view);

        $base = $namespace === null
            ? $this->rootPath . '/app/views'
            : $this->rootPath . '/app/Features/' . $namespace . '/Views';

        return $this->resolveInside($base, $path, "View [{$view}]");
    }

    public function findLayout(string $layout): string
    {
        [$namespace, $path] = $this->parse($layout);

        $base = $namespace === null
            ? $this->rootPath . '/app/views/layouts'
            : $this->rootPath . '/app/Features/' . $namespace . '/Views/layouts';

        return $this->resolveInside($base, $path, "Layout [{$layout}]");
    }

    /** @return array{0: ?string, 1: string} */
    private function parse(string $name): array
    {
        $name = trim($name);
        if ($name === '' || str_contains($name, "\0")) {
            throw new ViewException('A view name must be non-empty.');
        }

        if (!str_contains($name, '::')) {
            return [null, $name];
        }

        [$namespace, $path] = explode('::', $name, 2);
        if (!preg_match('/^[A-Za-z][A-Za-z0-9_-]*$/', $namespace) || $path === '') {
            throw new ViewException("Invalid namespaced view [{$name}].");
        }

        return [$namespace, $path];
    }

    private function resolveInside(string $base, string $path, string $label): string
    {
        $relative = str_replace(['.', '\\'], '/', trim($path, '/\\')) . '.php';
        if (str_contains($relative, '..') || str_starts_with($relative, '/')) {
            throw new ViewException("{$label} contains an invalid path.");
        }

        $candidate = $base . '/' . $relative;
        if (!is_file($candidate)) {
            throw new ViewNotFoundException("{$label} was not found at [{$candidate}].");
        }

        $realBase = realpath($base);
        $realFile = realpath($candidate);
        if ($realBase === false || $realFile === false || !str_starts_with($realFile, rtrim($realBase, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR)) {
            throw new ViewException("{$label} resolves outside its allowed directory.");
        }

        return $realFile;
    }
}

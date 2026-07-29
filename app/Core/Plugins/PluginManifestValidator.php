<?php

namespace Flex\Core\Plugins;

use Flex\Core\Flex;

class PluginManifestValidator
{
    private array $errors = [];
    private array $warnings = [];

    public function validate(
        array $manifest,
        string $pluginDirectory,
        string $pluginPath
    ): array {
        $this->errors = [];
        $this->warnings = [];

        $this->validateRequiredFields($manifest);
        $this->validateFieldTypes($manifest);
        $this->validateSlug($manifest, $pluginDirectory);
        $this->validateVersion($manifest);
        $this->validateAuthor($manifest);
        $this->validateRequirements($manifest);
        $this->validateAutoload($manifest, $pluginPath);
        $this->validateProvider($manifest, $pluginPath);
        $this->validateRoutes($manifest, $pluginPath);
        $this->validateOptionalLinks($manifest);
        $this->validateMigrations($manifest, $pluginPath);

        return [
            'valid' => $this->errors === [],
            'errors' => $this->errors,
            'warnings' => $this->warnings,
        ];
    }

    private function validateRequiredFields(array $manifest): void
    {
        $requiredFields = [
            'name',
            'slug',
            'description',
            'version',
            'type',
            'author',
            'requires',
            'autoload',
            'provider',
        ];

        foreach ($requiredFields as $field) {
            if (!array_key_exists($field, $manifest)) {
                $this->errors[] = "Липсва задължителното поле „{$field}“.";
                continue;
            }

            if (is_string($manifest[$field]) && trim($manifest[$field]) === '') {
                $this->errors[] = "Полето „{$field}“ не може да бъде празно.";
            }
        }
    }

    private function validateFieldTypes(array $manifest): void
    {
        $stringFields = [
            'name',
            'slug',
            'description',
            'version',
            'type',
            'license',
            'homepage',
            'repository',
            'provider',
        ];

        foreach ($stringFields as $field) {
            if (
                array_key_exists($field, $manifest) &&
                !is_string($manifest[$field])
            ) {
                $this->errors[] = "Полето „{$field}“ трябва да бъде текст.";
            }
        }

        $arrayFields = [
            'author',
            'requires',
            'autoload',
            'routes',
            'permissions',
            'features',
        ];

        foreach ($arrayFields as $field) {
            if (
                array_key_exists($field, $manifest) &&
                !is_array($manifest[$field])
            ) {
                $this->errors[] = "Полето „{$field}“ трябва да бъде масив или JSON обект.";
            }
        }

        $booleanFields = [
            'boot',
            'migrations',
            'seeders',
        ];

        foreach ($booleanFields as $field) {
            if (
                array_key_exists($field, $manifest) &&
                !is_bool($manifest[$field])
            ) {
                $this->errors[] = "Полето „{$field}“ трябва да бъде boolean стойност.";
            }
        }

        if (
            array_key_exists('admin_menu', $manifest) &&
            !is_bool($manifest['admin_menu']) &&
            !is_array($manifest['admin_menu'])
        ) {
            $this->errors[] = 'Полето „admin_menu“ трябва да бъде boolean или масив.';
        }

        if (
            array_key_exists('assets', $manifest) &&
            !is_bool($manifest['assets']) &&
            !is_array($manifest['assets'])
        ) {
            $this->errors[] = 'Полето „assets“ трябва да бъде boolean или масив.';
        }
    }

    private function validateSlug(
        array $manifest,
        string $pluginDirectory
    ): void {
        $slug = $manifest['slug'] ?? null;

        if (!is_string($slug) || trim($slug) === '') {
            return;
        }

        $slug = trim($slug);
        $pluginDirectory = trim($pluginDirectory);

        if (!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug)) {
            $this->errors[] = 'Полето „slug“ може да съдържа само малки латински букви, цифри и тирета.';
        }

        if ($slug !== $pluginDirectory) {
            $this->errors[] = "Slug „{$slug}“ не съвпада с директорията „{$pluginDirectory}“.";
        }
    }

    private function validateVersion(array $manifest): void
    {
        $version = $manifest['version'] ?? null;

        if (!is_string($version) || $version === '') {
            return;
        }

        if (!preg_match('/^\d+\.\d+\.\d+(?:-[0-9A-Za-z.-]+)?$/', $version)) {
            $this->errors[] = "Версията „{$version}“ не е валидна semantic version стойност.";
        }
    }

    private function validateAuthor(array $manifest): void
    {
        $author = $manifest['author'] ?? null;

        if (!is_array($author)) {
            return;
        }

        if (empty($author['name']) || !is_string($author['name'])) {
            $this->errors[] = 'Липсва валидно поле „author.name“.';
        }

        if (
            !empty($author['email']) &&
            !filter_var($author['email'], FILTER_VALIDATE_EMAIL)
        ) {
            $this->errors[] = 'Полето „author.email“ не съдържа валиден имейл адрес.';
        }

        if (
            !empty($author['website']) &&
            !filter_var($author['website'], FILTER_VALIDATE_URL)
        ) {
            $this->errors[] = 'Полето „author.website“ не съдържа валиден URL адрес.';
        }
    }

    private function validateRequirements(array $manifest): void
    {
        $requires = $manifest['requires'] ?? null;

        if (!is_array($requires)) {
            return;
        }

        if (empty($requires['php']) || !is_string($requires['php'])) {
            $this->errors[] = 'Липсва валидно поле „requires.php“.';
        } elseif (
            !$this->matchesVersionConstraint(
                PHP_VERSION,
                $requires['php']
            )
        ) {
            $this->errors[] = sprintf(
                'Плъгинът изисква PHP %s, а текущата версия е %s.',
                $requires['php'],
                PHP_VERSION
            );
        }

        if (empty($requires['flex']) || !is_string($requires['flex'])) {
            $this->errors[] = 'Липсва валидно поле „requires.flex“.';
        } elseif (Flex::VERSION) {
            if (
                !$this->matchesVersionConstraint(
                    Flex::VERSION,
                    $requires['flex']
                )
            ) {
                $this->errors[] = sprintf(
                    'Плъгинът изисква Flex CMS %s, а текущата версия е %s.',
                    $requires['flex'],
                    Flex::VERSION
                );
            }
        } else {
            $this->warnings[] = 'FLEX_VERSION не е дефинирана и съвместимостта с Flex CMS не може да бъде проверена.';
        }
    }

    private function validateAutoload(
        array $manifest,
        string $pluginPath
    ): void {
        $autoload = $manifest['autoload'] ?? null;

        if (!is_array($autoload)) {
            return;
        }

        $psr4 = $autoload['psr-4'] ?? null;

        if (!is_array($psr4) || $psr4 === []) {
            $this->errors[] = 'Липсва валидна конфигурация „autoload.psr-4“.';
            return;
        }

        foreach ($psr4 as $namespace => $relativePath) {
            if (!is_string($namespace) || trim($namespace) === '') {
                $this->errors[] = 'Namespace ключът в „autoload.psr-4“ не е валиден.';
                continue;
            }

            if (!str_ends_with($namespace, '\\')) {
                $this->errors[] = "Namespace „{$namespace}“ трябва да завършва с обратна наклонена черта.";
            }

            if (!is_string($relativePath) || trim($relativePath) === '') {
                $this->errors[] = "Autoload пътят за namespace „{$namespace}“ не е валиден.";
                continue;
            }

            $absolutePath = $pluginPath . DIRECTORY_SEPARATOR .
                trim($relativePath, '/\\');

            if (!is_dir($absolutePath)) {
                $this->errors[] = "Autoload директорията „{$relativePath}“ не съществува.";
            }
        }
    }

    private function validateProvider(
        array $manifest,
        string $pluginPath
    ): void {
        $provider = $manifest['provider'] ?? null;

        if (!is_string($provider) || trim($provider) === '') {
            return;
        }

        $autoload = $manifest['autoload']['psr-4'] ?? [];

        if (!is_array($autoload)) {
            return;
        }

        foreach ($autoload as $namespace => $relativePath) {
            if (!str_starts_with($provider, $namespace)) {
                continue;
            }

            $relativeClass = substr($provider, strlen($namespace));

            $providerPath = $pluginPath . DIRECTORY_SEPARATOR .
                trim($relativePath, '/\\') . DIRECTORY_SEPARATOR .
                str_replace('\\', DIRECTORY_SEPARATOR, $relativeClass) .
                '.php';

            if (!is_file($providerPath)) {
                $this->errors[] = "Provider файлът „{$providerPath}“ не съществува.";
            }

            return;
        }

        $this->errors[] = "Provider класът „{$provider}“ не съответства на autoload namespace.";
    }

    private function validateRoutes(
        array $manifest,
        string $pluginPath
    ): void {
        $routes = $manifest['routes'] ?? [];

        if (!is_array($routes)) {
            return;
        }

        $allowedRouteTypes = [
            'web',
            'admin',
            'api',
        ];

        foreach ($routes as $type => $relativePath) {
            if (!in_array($type, $allowedRouteTypes, true)) {
                $this->warnings[] = "Непознат route тип „{$type}“.";
            }

            if (!is_string($relativePath) || trim($relativePath) === '') {
                $this->errors[] = "Route пътят за „{$type}“ не е валиден.";
                continue;
            }

            $absolutePath = $pluginPath . DIRECTORY_SEPARATOR .
                trim($relativePath, '/\\');

            if (!is_file($absolutePath)) {
                $this->errors[] = "Route файлът „{$relativePath}“ не съществува.";
            }
        }
    }

    private function validateOptionalLinks(array $manifest): void
    {
        foreach (['homepage', 'repository'] as $field) {
            if (
                !empty($manifest[$field]) &&
                !filter_var($manifest[$field], FILTER_VALIDATE_URL)
            ) {
                $this->warnings[] = "Полето „{$field}“ не съдържа валиден URL адрес.";
            }
        }

        if (empty($manifest['license'])) {
            $this->warnings[] = 'Не е зададен лиценз за плъгина.';
        }

        if (empty($manifest['homepage'])) {
            $this->warnings[] = 'Не е зададена страница на плъгина.';
        }

        if (empty($manifest['repository'])) {
            $this->warnings[] = 'Не е зададен repository адрес.';
        }
    }

    private function matchesVersionConstraint(
        string $currentVersion,
        string $constraint
    ): bool {
        $constraint = trim($constraint);

        if (preg_match('/^(>=|<=|>|<|=|\^|~)?\s*(\d+(?:\.\d+){0,2})$/', $constraint, $matches)) {
            $operator = $matches[1] ?: '=';
            $requiredVersion = $this->normalizeVersion($matches[2]);

            return match ($operator) {
                '>=' => version_compare($currentVersion, $requiredVersion, '>='),
                '<=' => version_compare($currentVersion, $requiredVersion, '<='),
                '>' => version_compare($currentVersion, $requiredVersion, '>'),
                '<' => version_compare($currentVersion, $requiredVersion, '<'),
                '=' => version_compare($currentVersion, $requiredVersion, '='),
                '^' => $this->matchesCaretConstraint(
                    $currentVersion,
                    $requiredVersion
                ),
                '~' => $this->matchesTildeConstraint(
                    $currentVersion,
                    $requiredVersion
                ),
                default => false,
            };
        }

        $this->warnings[] = "Ограничението за версия „{$constraint}“ не може да бъде автоматично проверено.";

        return true;
    }

    private function normalizeVersion(string $version): string
    {
        $parts = explode('.', $version);

        while (count($parts) < 3) {
            $parts[] = '0';
        }

        return implode('.', array_slice($parts, 0, 3));
    }

    private function matchesCaretConstraint(
        string $currentVersion,
        string $requiredVersion
    ): bool {
        $parts = array_map('intval', explode('.', $requiredVersion));

        if ($parts[0] > 0) {
            $upperBound = ($parts[0] + 1) . '.0.0';
        } elseif ($parts[1] > 0) {
            $upperBound = '0.' . ($parts[1] + 1) . '.0';
        } else {
            $upperBound = '0.0.' . ($parts[2] + 1);
        }

        return version_compare($currentVersion, $requiredVersion, '>=')
            && version_compare($currentVersion, $upperBound, '<');
    }

    private function matchesTildeConstraint(
        string $currentVersion,
        string $requiredVersion
    ): bool {
        $parts = array_map('intval', explode('.', $requiredVersion));

        $upperBound = $parts[0] . '.' . ($parts[1] + 1) . '.0';

        return version_compare($currentVersion, $requiredVersion, '>=')
            && version_compare($currentVersion, $upperBound, '<');
    }

    private function validateMigrations(
        array $manifest,
        string $pluginPath
    ): void {
        if (!($manifest['migrations'] ?? false)) {
            return;
        }

        $migrationsPath = $pluginPath
            . DIRECTORY_SEPARATOR
            . 'database'
            . DIRECTORY_SEPARATOR
            . 'migrations';

        if (!is_dir($migrationsPath)) {
            $this->errors[] =
                'Полето „migrations“ е true, но директорията '
                . 'database/migrations не съществува.';

            return;
        }

        foreach (glob($migrationsPath . '/*.php') ?: [] as $file) {
            $name = pathinfo($file, PATHINFO_FILENAME);

            if (!preg_match('/^\d{14}_[a-z][a-z0-9_]*$/', $name)) {
                $this->errors[] =
                    "Migration файлът „{$name}.php“ има невалидно име.";
            }
        }
    }
}

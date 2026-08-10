<?php

declare(strict_types=1);

namespace Flex\Features\AdminUI\Services;

use Flex\Core\Assets\AdminAssetRegistry;
use Flex\Core\Assets\ViteAssetResolver;
use Flex\Features\AdminUI\Configuration\AdminUIConfig;

final readonly class AdminUIAssets
{
    private const ADMIN_UI_SCRIPT =
        'resources/ui/admin/index.js';

    private const ADMIN_UI_STYLE =
        'resources/ui/admin/styles/index.css';

    public function __construct(
        private AdminUIConfig $config,
        private AdminAssetRegistry $registry,
        private ViteAssetResolver $vite
    ) {
    }

    /**
     * Рендерира основните AdminUI стилове
     * и всички регистрирани Feature/Plugin стилове.
     */
    public function styles(): string
    {
        $styles = [];

        /*
         * Основният AdminUI stylesheet.
         */
        $this->addStyle(
            $styles,
            $this->vite->asset(
                self::ADMIN_UI_STYLE
            )
        );

        /*
         * CSS файлове, регистрирани директно
         * от Features/Plugins.
         */
        foreach (
            $this->registry->styles()
            as $entry
        ) {
            $this->addStyle(
                $styles,
                $this->vite->asset(
                    $entry
                )
            );
        }

        /*
         * Ако JS entry импортва CSS:
         *
         * import "./settings.css";
         *
         * Vite ще го запише като CSS dependency
         * в manifest-а.
         *
         * Зареждаме и тези стилове автоматично.
         */
        foreach (
            $this->registry->scripts()
            as $entry
        ) {
            foreach (
                $this->vite->css(
                    $entry
                )
                as $stylesheet
            ) {
                $this->addStyle(
                    $styles,
                    $stylesheet
                );
            }
        }

        return implode(
            PHP_EOL,
            array_values($styles)
        );
    }

    /**
     * Рендерира основния AdminUI JavaScript
     * и всички регистрирани Feature/Plugin scripts.
     */
    public function scripts(): string
    {
        $scripts = [];

        /*
         * Основният AdminUI entry.
         */
        $this->addScript(
            $scripts,
            $this->vite->asset(
                self::ADMIN_UI_SCRIPT
            )
        );

        /*
         * Feature / Plugin scripts.
         */
        foreach (
            $this->registry->scripts()
            as $entry
        ) {
            $this->addScript(
                $scripts,
                $this->vite->asset(
                    $entry
                )
            );
        }

        return implode(
            PHP_EOL,
            array_values($scripts)
        );
    }

    /**
     * Turbo meta tags.
     */
    public function turboMetaTags(): string
    {
        if (
            !$this->config->turboEnabled()
        ) {
            return '';
        }

        return implode(
            PHP_EOL,
            [
                '<meta name="turbo-prefetch" content="false">',
                '<meta name="view-transition" content="same-origin">',
            ]
        );
    }

    /**
     * Задава темата преди зареждането
     * на основния CSS.
     *
     * Така избягваме премигването между
     * светла и тъмна тема.
     */
    public function themeBootstrap(
        ?string $userPreference = null
    ): string {
        $preference =
            $this->normalizePreference(
                $userPreference
                ?? $this->config
                    ->defaultTheme()
            );

        $encodedPreference =
            json_encode(
                $preference,
                JSON_THROW_ON_ERROR
                | JSON_HEX_TAG
                | JSON_HEX_AMP
                | JSON_HEX_APOS
                | JSON_HEX_QUOT
            );

        return <<<HTML
        <script>
        (function () {
            const storageKey = "flex.admin.theme";

            const fallbackPreference =
                {$encodedPreference};

            const supported = new Set([
                "light",
                "dark",
                "system"
            ]);

            let preference =
                fallbackPreference;

            try {
                const stored =
                    window.localStorage.getItem(
                        storageKey
                    );

                if (
                    stored !== null
                    && supported.has(stored)
                ) {
                    preference = stored;
                }
            } catch (error) {
                /*
                 * localStorage може да бъде
                 * недостъпен.
                 */
            }

            if (!supported.has(preference)) {
                preference = "system";
            }

            const systemDark =
                window.matchMedia(
                    "(prefers-color-scheme: dark)"
                ).matches;

            const theme =
                preference === "system"
                    ? (
                        systemDark
                            ? "dark"
                            : "light"
                    )
                    : preference;

            const root =
                document.documentElement;

            root.dataset.theme =
                theme;

            root.dataset.themePreference =
                preference;

            root.style.colorScheme =
                theme;
        })();
        </script>
        HTML;
    }

    /**
     * Добавя stylesheet без дублиране.
     *
     * @param array<string, string> $styles
     */
    private function addStyle(
        array &$styles,
        string $url
    ): void {
        if ($url === '') {
            return;
        }

        $styles[$url] = sprintf(
            '<link rel="stylesheet" href="%s">',
            $this->escape($url)
        );
    }

    /**
     * Добавя ES module script без дублиране.
     *
     * @param array<string, string> $scripts
     */
    private function addScript(
        array &$scripts,
        string $url
    ): void {
        if ($url === '') {
            return;
        }

        $scripts[$url] = sprintf(
            '<script type="module" src="%s"></script>',
            $this->escape($url)
        );
    }

    private function escape(
        string $value
    ): string {
        return htmlspecialchars(
            $value,
            ENT_QUOTES,
            'UTF-8'
        );
    }

    private function normalizePreference(
        string $preference
    ): string {
        return in_array(
            $preference,
            [
                'light',
                'dark',
                'system',
            ],
            true
        )
            ? $preference
            : 'system';
    }
}

<?php

declare(strict_types=1);

namespace Flex\Features\AdminUI\Services;

use Flex\Core\Vite;
use Flex\Features\AdminUI\Configuration\AdminUIConfig;

final readonly class AdminUIAssets
{
    private const STYLE_ENTRY =
        'resources/ui/admin/styles/index.css';

    private const SCRIPT_ENTRY =
        'resources/ui/admin/index.js';

    public function __construct(
        private AdminUIConfig $config
    ) {
    }

    public function styles(): string
    {
        $html = (string) Vite::entry(
            self::STYLE_ENTRY
        )->port(
            $this->config->vitePort()
        );

        return $this->addTurboTracking($html);
    }

    public function scripts(): string
    {
        $html = (string) Vite::entry(
            self::SCRIPT_ENTRY
        )->port(
            $this->config->vitePort()
        );

        return $this->addTurboTracking($html);
    }

    public function turboMetaTags(): string
    {
        if (!$this->config->turboEnabled()) {
            return '';
        }

        return implode(PHP_EOL, [
            '<meta name="turbo-prefetch" content="false">',
            '<meta name="view-transition" content="same-origin">',
        ]);
    }

    /**
     * Връща синхронен inline script, който задава
     * темата преди зареждането на CSS.
     *
     * Така предотвратяваме премигване между
     * светла и тъмна тема.
     */
    public function themeBootstrap(
        ?string $userPreference = null
    ): string {
        $preference = $this->normalizePreference(
            $userPreference
                ?? $this->config->defaultTheme()
        );

        $encodedPreference = json_encode(
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
    const fallbackPreference = {$encodedPreference};
    const supported = new Set([
        "light",
        "dark",
        "system"
    ]);

    let preference = fallbackPreference;

    try {
        const stored = window.localStorage.getItem(
            storageKey
        );

        if (supported.has(stored)) {
            preference = stored;
        }
    } catch (error) {
        // localStorage може да бъде недостъпен.
    }

    if (!supported.has(preference)) {
        preference = "system";
    }

    const systemDark = window.matchMedia(
        "(prefers-color-scheme: dark)"
    ).matches;

    const theme = preference === "system"
        ? (systemDark ? "dark" : "light")
        : preference;

    const root = document.documentElement;

    root.dataset.theme = theme;
    root.dataset.themePreference = preference;
    root.style.colorScheme = theme;
})();
</script>
HTML;
    }

    private function normalizePreference(
        string $preference
    ): string {
        return in_array(
            $preference,
            ['light', 'dark', 'system'],
            true
        ) ? $preference : 'system';
    }

    private function addTurboTracking(
        string $html
    ): string {
        if ($html === '') {
            return '';
        }

        $html = str_replace(
            '<link ',
            '<link data-turbo-track="reload" ',
            $html
        );

        return str_replace(
            '<script ',
            '<script data-turbo-track="reload" ',
            $html
        );
    }
}
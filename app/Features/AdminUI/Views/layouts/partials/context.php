<?php

declare(strict_types=1);
use Flex\Features\Authentication\Providers\AuthProvider;

$sidebarItems = isset($adminUISidebar['items']) && is_array($adminUISidebar['items']) ? $adminUISidebar['items'] : [];

$sidebarPosition = in_array($adminUISidebar['position'] ?? null, ['left', 'right'], true)
    ? $adminUISidebar['position']
    : 'left';

$currentUser = AuthProvider::user();

$userName = is_string($currentUser?->fullname ?? null) ? $currentUser->fullname : 'Гост';
$userEmail = is_string($currentUser?->email ?? null) ? $currentUser->email : '';

$userOptions = $currentUser?->options ?? [];

if (!is_array($userOptions)) {
    $userOptions = [];
}

$userTheme = $userOptions['theme'] ?? null;

$themePreference = in_array($userTheme, ['light', 'dark', 'system'], true)
    ? $userTheme
    : (is_string($adminUIConfig['defaultTheme'] ?? null) ? $adminUIConfig['defaultTheme'] : 'system');

$userInitial = function_exists('mb_substr')
    ? mb_substr($userName, 0, 1, 'UTF-8')
    : substr($userName, 0, 1);

$adminName = is_string($adminUIConfig['name'] ?? null) ? $adminUIConfig['name'] : 'Flex CMS';
$turboEnabled = ($adminUIConfig['turboEnabled'] ?? true) === true;

$escape = static fn(mixed $value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');

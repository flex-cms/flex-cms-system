<?php

if (!function_exists('base_path')) {
    function base_path(string $path = ''): string
    {
        return __DIR__ . '/' . $path;
    }
}

if (!function_exists('themes_path')) {
    function themes_path(string $path = ''): string
    {
        return __DIR__ . '/themes/' . $path;
    }
}

if (!function_exists('plugins_path')) {
    function plugins_path(string $path = ''): string
    {
        return __DIR__ . '/plugins/' . ltrim($path, '/');
    }
}
<?php

namespace Flex\Core\Plugins\Contracts;

use Flex\Core\Plugins\UninstallOptions;

interface PluginInstallerInterface
{
    public static function install(): void;

    public static function activate(): void;

    public static function deactivate(): void;

    public static function uninstall(UninstallOptions $options): void;
}
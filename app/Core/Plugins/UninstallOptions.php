<?php

namespace Flex\Core\Plugins;

final readonly class UninstallOptions
{
    public function __construct(
        public bool $deleteData = false,
        public bool $deleteSettings = true,
        public bool $deleteCache = true,
        public bool $deleteLogs = false,
        public bool $deleteUploads = false,
    ) {
    }

    public static function preserveData(): self
    {
        return new self(
            deleteData: false,
            deleteSettings: true,
            deleteCache: true,
        );
    }

    public static function removeEverything(): self
    {
        return new self(
            deleteData: true,
            deleteSettings: true,
            deleteCache: true,
            deleteLogs: true,
            deleteUploads: true,
        );
    }
}

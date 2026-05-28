<?php

namespace Flex\Core\Services;

class Cache
{
    public static function clear($type)
    {
        $dir = __DIR__ . '/../../storage/cache/' . $type;
        array_map('unlink', glob("$dir/*.*"));
    }
}
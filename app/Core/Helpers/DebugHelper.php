<?php

namespace Flex\Core\Helpers;

use Flex\Models\Setting;

class DebugHelper
{
    public static function log($data, $die = false)
    {
        if (Setting::getValue('debug_mode', false)) {
            echo '<pre style="background: #000; color: #0f0; padding: 10px; z-index: 9999; position: relative;">';
            var_dump($data);
            echo '</pre>';

            if ($die)
                die();
        }
    }
}
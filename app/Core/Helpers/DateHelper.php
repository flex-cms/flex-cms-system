<?php

namespace Flex\Core\Helpers;

use Carbon\Carbon;
use Flex\Models\Setting;

class DateHelper
{
    public static function format($date = null, bool $includeTime = false)
    {
        $timezone = Setting::get('timezone', 'Europe/Sofia');
        $lang = Setting::get('site_default_lang', 'bg');
        
        $format = Setting::get('date_format', 'd.m.Y');
        
        if ($includeTime) {
            $format .= ' : H:i';
        }

        $carbonDate = $date ? Carbon::parse($date) : Carbon::now();
        $carbonDate->setTimezone($timezone);
        $carbonDate->locale($lang);

        return $carbonDate->translatedFormat($format);
    }
}
<?php

namespace Flex\Core\Helpers;

use Carbon\Carbon;
use Flex\Models\Setting;

class DateHelper
{
    public static function format($date = null, bool $includeTime = false)
    {
        $timezone = Setting::getValue('timezone', 'Europe/Sofia');
        $lang = Setting::getValue('site_default_lang', 'bg');

        $format = Setting::getValue('date_format', 'd.m.Y');

        if ($includeTime) {
            $format .= ' : H:i';
        }

        $carbonDate = $date ? Carbon::parse($date) : Carbon::now();
        $carbonDate->setTimezone($timezone);
        $carbonDate->locale($lang);

        return $carbonDate->translatedFormat($format);
    }

    public static function iso($date = null)
    {
        $timezone = Setting::getValue('timezone', 'Europe/Sofia');

        return Carbon::parse($date)
            ->setTimezone($timezone)
            ->toIso8601String();
    }
}
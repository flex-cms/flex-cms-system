<?php

namespace Flex\Core\Helpers;

use Carbon\Carbon;
use Flex\Models\Setting;

class DateHelper
{
    public static function format()
    {
        $timezone = Setting::get('timezone', 'Europe/Sofia');
        $format = Setting::get('date_format', 'd.m.Y H:i');
        $lang = Setting::get('site_default_lang', 'bg');

        $date = Carbon::now($timezone);

        $date->locale($lang);

        return $date->translatedFormat($format);
    }
}
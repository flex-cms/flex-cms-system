<?php

namespace Flex\Core\Utils;

use DateTime;
use DateTimeZone;
use Exception;

class TimeHelper
{
    public static function nowUtc(): string
    {
        return gmdate('Y-m-d H:i:s');
    }

    public static function addTime(string $interval): string
    {
        $date = new DateTime('now', new DateTimeZone('UTC'));
        $date->modify($interval);
        return $date->format('Y-m-d H:i:s');
    }

    public static function toLocal(string $utcDateTime, string $timezone = 'Europe/Sofia'): string
    {
        try {
            $date = new DateTime($utcDateTime, new DateTimeZone('UTC'));
            $date->setTimezone(new DateTimeZone($timezone));
            return $date->format('d.m.Y H:i:s');
        } catch (Exception $e) {
            return $utcDateTime;
        }
    }

    public static function elapsedSeconds(string $utcDateTime): int
    {
        $lastTime = strtotime($utcDateTime . ' UTC');
        $currentTime = time();
        return $currentTime - $lastTime;
    }
}
<?php

namespace App\Support\Formatting;

readonly class TimeFormatter
{
    public static function formatMinutesToReadableHours(int $minutes): string
    {
        $hours = intdiv($minutes, 60);
        $remainder = $minutes % 60;

        return sprintf('%d:%02dh', $hours, $remainder);
    }
}

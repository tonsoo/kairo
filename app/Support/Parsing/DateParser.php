<?php

declare(strict_types=1);

namespace App\Support\Parsing;

use Carbon\CarbonImmutable;
use DateTimeInterface;
use LogicException;
use Throwable;

final class DateParser
{
    public const string localDateFormat = 'Y-m-d';
    public const string localTimeFormat = 'H:i';

    public static function nowInTimezone(string $timezone): CarbonImmutable
    {
        return CarbonImmutable::now($timezone);
    }

    public static function parseLocalDate(string $value, string $timezone, string $field): CarbonImmutable
    {
        return self::parse(
            value: $value,
            format: '!'.self::localDateFormat,
            field: $field,
            timezone: $timezone,
        );
    }

    public static function parseAtomDateTime(string $value, string $field): CarbonImmutable
    {
        return self::parse(
            value: $value,
            format: DateTimeInterface::ATOM,
            field: $field,
        );
    }

    public static function parseLocalTimeOnDate(
        string $time,
        CarbonImmutable $date,
        string $timezone,
        string $field,
    ): CarbonImmutable {
        return self::parse(
            value: $date->format(self::localDateFormat).' '.$time,
            format: self::localDateFormat.' '.self::localTimeFormat,
            field: $field,
            timezone: $timezone,
        );
    }

    private static function parse(
        string $value,
        string $format,
        string $field,
        ?string $timezone = null,
    ): CarbonImmutable {
        try {
            $dateTime = CarbonImmutable::createFromFormat(
                $format,
                $value,
                $timezone,
            );
        } catch (Throwable) {
            throw new LogicException(sprintf('Validated %s value could not be parsed.', $field));
        }

        if ($dateTime === false) {
            throw new LogicException(sprintf('Validated %s value could not be parsed.', $field));
        }

        return $dateTime;
    }
}

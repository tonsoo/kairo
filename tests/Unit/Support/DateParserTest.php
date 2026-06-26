<?php

declare(strict_types=1);

use App\Support\Parsing\DateParser;
use Carbon\CarbonImmutable;

test('it parses a local date in the provided timezone', function () {
    $date = DateParser::parseLocalDate('2026-06-26', 'America/Sao_Paulo', 'from');

    expect($date)
        ->toBeInstanceOf(CarbonImmutable::class)
        ->and($date->format('Y-m-d H:i:s'))->toBe('2026-06-26 00:00:00')
        ->and($date->timezoneName)->toBe('America/Sao_Paulo');
});

test('it parses an atom datetime', function () {
    $dateTime = DateParser::parseAtomDateTime('2026-06-26T14:30:00-03:00', 'at');

    expect($dateTime)
        ->toBeInstanceOf(CarbonImmutable::class)
        ->and($dateTime->format('Y-m-d\TH:i:sP'))->toBe('2026-06-26T14:30:00-03:00');
});

test('it parses a local time on a given date', function () {
    $date = DateParser::parseLocalDate('2026-06-26', 'America/Sao_Paulo', 'effective_from');
    $dateTime = DateParser::parseLocalTimeOnDate('09:15', $date, 'America/Sao_Paulo', 'starts_at');

    expect($dateTime)
        ->toBeInstanceOf(CarbonImmutable::class)
        ->and($dateTime->format('Y-m-d H:i:s'))->toBe('2026-06-26 09:15:00')
        ->and($dateTime->timezoneName)->toBe('America/Sao_Paulo');
});

test('it throws a logic exception for invalid atom values', function () {
    expect(fn () => DateParser::parseAtomDateTime('invalid', 'at'))
        ->toThrow(LogicException::class, 'Validated at value could not be parsed.');
});

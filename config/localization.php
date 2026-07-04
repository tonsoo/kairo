<?php

return [
    'driver' => env('APP_LOCALE_DRIVER', 'url'),

    'supported_locales' => array_values(array_filter(array_map(
        static fn (string $locale) => trim($locale),
        explode(',', (string) env('APP_SUPPORTED_LOCALES', 'en,pt-BR')),
    ))),

    'default_locale' => env('APP_LOCALE', 'en'),

    'drivers' => [
        'url' => [],
        'domain' => [
            'pattern' => env('APP_LOCALE_DOMAIN_PATTERN', '{locale}.localhost'),
        ],
    ],
];

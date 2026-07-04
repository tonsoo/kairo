<?php

namespace App\Traits;

use App\Support\Localization\LocaleRouting;
use InvalidArgumentException;

trait ValidatesLocaleRoutes
{
    protected function ensureSupportedLocale(LocaleRouting $localeRouting, string $locale): void
    {
        if (! in_array($locale, $localeRouting->supportedLocales(), true)) {
            throw new InvalidArgumentException(sprintf('Unsupported locale [%s].', $locale));
        }
    }
}

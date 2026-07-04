<?php

declare(strict_types=1);

namespace App\Support\Localization;

use Closure;
use Illuminate\Http\Request;

interface LocaleDriver
{
    /**
     * @param  Closure(): void  $routes
     */
    public function registerRoutes(LocaleRouting $localeRouting, Closure $routes): void;

    /**
     * @return array<string, string>
     */
    public function defaults(string $locale): array;

    public function resolveLocale(Request $request, LocaleRouting $localeRouting): string;

    public function localizeUrl(Request $request, LocaleRouting $localeRouting, string $locale): string;
}

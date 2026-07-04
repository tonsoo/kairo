<?php

declare(strict_types=1);

namespace App\Support\Localization;

use App\Support\Localization\Drivers\DomainLocaleDriver;
use App\Support\Localization\Drivers\UrlLocaleDriver;
use Closure;
use Illuminate\Http\Request;
use RuntimeException;

final class LocaleRouting
{
    private ?LocaleDriver $driver = null;

    public function driverKey(): string
    {
        return (string) config('localization.driver', 'url');
    }

    public function defaultLocale(): string
    {
        $defaultLocale = (string) config('localization.default_locale', 'en');

        if (! in_array($defaultLocale, $this->supportedLocales(), true)) {
            throw new RuntimeException('localization.default_locale must be included in localization.supported_locales.');
        }

        return $defaultLocale;
    }

    /**
     * @return list<string>
     */
    public function supportedLocales(): array
    {
        $locales = config('localization.supported_locales', []);

        if (! is_array($locales) || $locales === []) {
            throw new RuntimeException('localization.supported_locales must contain at least one locale.');
        }

        return array_values($locales);
    }

    public function localePattern(): string
    {
        return implode('|', array_map(
            static fn (string $locale) => preg_quote($locale, '/'),
            $this->supportedLocales(),
        ));
    }

    /**
     * @param  Closure(): void  $routes
     */
    public function registerRoutes(Closure $routes): void
    {
        $this->driver()->registerRoutes($this, $routes);
    }

    public function resolveLocale(Request $request): string
    {
        return $this->driver()->resolveLocale($request, $this);
    }

    public function localizedUrl(Request $request, string $locale): string
    {
        return $this->driver()->localizeUrl($request, $this, $locale);
    }

    /**
     * @return array<string, string>
     */
    public function routeDefaults(string $locale): array
    {
        return $this->driver()->defaults($locale);
    }

    private function driver(): LocaleDriver
    {
        if ($this->driver !== null) {
            return $this->driver;
        }

        $this->driver = match ($this->driverKey()) {
            'url' => new UrlLocaleDriver,
            'domain' => new DomainLocaleDriver,
            default => throw new RuntimeException('Unsupported localization driver ['.$this->driverKey().'].')
        };

        return $this->driver;
    }
}

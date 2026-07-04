<?php

declare(strict_types=1);

namespace App\Support\Localization\Drivers;

use App\Http\Middleware\HandleLocale;
use App\Support\Localization\LocaleDriver;
use App\Support\Localization\LocaleRouting;
use App\Traits\ValidatesLocaleRoutes;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Route as RouteFacade;
use LogicException;
use RuntimeException;

final class DomainLocaleDriver implements LocaleDriver
{
    use ValidatesLocaleRoutes;

    /**
     * @param  Closure(): void  $routes
     */
    public function registerRoutes(LocaleRouting $localeRouting, Closure $routes): void
    {
        RouteFacade::group([
            'domain' => $this->domainPattern(),
            'where' => [
                'locale' => $localeRouting->localePattern(),
            ],
            'middleware' => [HandleLocale::class],
        ], $routes);
    }

    /**
     * @return array<string, string>
     */
    public function defaults(string $locale): array
    {
        return [
            'locale' => $locale,
        ];
    }

    public function resolveLocale(Request $request, LocaleRouting $localeRouting): string
    {
        $locale = $request->route('locale');

        return is_string($locale) && in_array($locale, $localeRouting->supportedLocales(), true)
            ? $locale
            : $localeRouting->defaultLocale();
    }

    public function localizeUrl(Request $request, LocaleRouting $localeRouting, string $locale): string
    {
        $this->ensureSupportedLocale($localeRouting, $locale);

        $route = $request->route();

        if (! $route instanceof Route) {
            throw new LogicException('Cannot localize URL without a resolved route.');
        }

        $routeName = $route->getName();

        if (! is_string($routeName)) {
            throw new LogicException('Cannot localize URL for unnamed routes.');
        }

        return route(
            name: $routeName,
            parameters: [
                ...$route->parameters(),
                'locale' => $locale,
                ...$request->query(),
            ],
        );
    }

    private function domainPattern(): string
    {
        $pattern = (string) config('localization.drivers.domain.pattern');

        if (! str_contains($pattern, '{locale}')) {
            throw new RuntimeException(
                'The domain locale driver requires a {locale} placeholder in localization.drivers.domain.pattern.'
            );
        }

        return $pattern;
    }
}

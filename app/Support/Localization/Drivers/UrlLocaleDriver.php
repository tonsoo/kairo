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

final class UrlLocaleDriver implements LocaleDriver
{
    use ValidatesLocaleRoutes;

    /**
     * @param  Closure(): void  $routes
     */
    public function registerRoutes(LocaleRouting $localeRouting, Closure $routes): void
    {
        RouteFacade::get('/', fn () => redirect()->route('home'))->name('root');

        RouteFacade::group([
            'prefix' => '{locale?}',
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
        $route = $request->route();

        if (! $route instanceof Route) {
            abort(404);
        }

        $routeName = $route->getName();

        if (! is_string($routeName) || $routeName === '') {
            abort(404);
        }

        $parameters = $route->parametersWithoutNulls();

        $allowedParameterNames = $route->parameterNames();

        $parameters = array_intersect_key(
            $parameters,
            array_flip($allowedParameterNames),
        );

        $parameters['locale'] = $locale;

        return route($routeName, $parameters);
    }
}

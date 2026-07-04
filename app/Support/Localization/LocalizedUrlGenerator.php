<?php

declare(strict_types=1);

namespace App\Support\Localization;

use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use InvalidArgumentException;

final readonly class LocalizedUrlGenerator
{
    public function __construct(
        private Router $router,
        private LocaleRouting $localeRouting,
    ) {}

    /**
     * @return array<string, string>
     */
    public function routeDefaults(string $locale): array
    {
        return $this->localeRouting->routeDefaults($locale);
    }

    /**
     * @return array<int, array{code: string, url: string}>
     */
    public function localeOptions(Request $request): array
    {
        return array_map(fn (string $locale) => [
            'code' => $locale,
            'url' => $this->localeRouting->localizedUrl($request, $locale),
        ], $this->localeRouting->supportedLocales());
    }

    public function currentUrl(Request $request): string
    {
        return $request->fullUrl();
    }

    /**
     * @param  array<string, mixed>  $parameters
     */
    public function url(string $routeName, ?string $locale = null, array $parameters = [], bool $absolute = true): string
    {
        $route = $this->router->getRoutes()->getByName($routeName);

        if (! $route instanceof Route) {
            throw new InvalidArgumentException('Unknown route ['.$routeName.'].');
        }

        $targetLocale = $locale ?? $this->localeRouting->defaultLocale();

        return route(
            $routeName,
            array_replace($this->routeDefaultsForRoute($route, $targetLocale), $parameters),
            $absolute,
        );
    }

    /**
     * @return array<string, string>
     */
    private function routeDefaultsForRoute(Route $route, string $locale): array
    {
        return array_intersect_key(
            $this->routeDefaults($locale),
            array_flip($route->parameterNames()),
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Support\Localization\LocaleRouting;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

final readonly class SitemapController
{
    public function __construct(
        private UrlGenerator $url,
        private LocaleRouting $localeRouting,
    ) {}

    public function __invoke(): Response
    {
        $sitemap = Sitemap::create();

        foreach ($this->pages() as $page) {
            $name = $page['name'] ?? null;
            assert($name !== null, 'Page name cannot be null');

            $urls = $this->resolveUrlsForSitemap(
                name: $name,
                isLocalized: $page['localized'] ?? false,
                parameters: $page['parameters'] ?? [],
            );

            foreach ($urls as $url) {
                $sitemap->add($url);
            }
        }

        return response($sitemap->render(), 200)
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    /**
     * @return array<int, array{name: string, localized?: bool, parameters?: array<string, mixed>}>
     */
    private function pages(): array
    {
        return config('sitemap.routes', []);
    }

    /**
     * @param  array<string, mixed>  $parameters
     * @return Collection<int, Url>
     */
    private function resolveUrlsForSitemap(
        string $name,
        bool $isLocalized,
        array $parameters = [],
    ): Collection {
        $lastMod = CarbonImmutable::now()->startOfDay();

        if (! $isLocalized) {
            return collect([
                Url::create($this->url->route($name, $parameters))
                    ->setLastModificationDate($lastMod),
            ]);
        }

        return collect($this->localeRouting->supportedLocales())
            ->map(
                fn (string $locale) => $this->localizedSitemapUrl(
                    routeName: $name,
                    locale: $locale,
                    lastMod: $lastMod,
                    parameters: $parameters,
                )
            );
    }

    /**
     * @param  array<string, mixed>  $parameters
     */
    private function localizedSitemapUrl(
        string $routeName,
        string $locale,
        CarbonImmutable $lastMod,
        array $parameters = [],
    ): Url {
        $url = Url::create($this->localizedUrl($routeName, $locale, $parameters))
            ->setLastModificationDate($lastMod);

        foreach ($this->localeRouting->supportedLocales() as $alternateLocale) {
            $url->addAlternate(
                $this->localizedUrl($routeName, $alternateLocale, $parameters),
                $alternateLocale,
            );
        }

        return $url;
    }

    /**
     * @param  array<string, mixed>  $parameters
     */
    private function localizedUrl(string $routeName, string $locale, array $parameters = []): string
    {
        return $this->url->route($routeName, [
            ...$parameters,
            'locale' => $locale,
        ]);
    }
}

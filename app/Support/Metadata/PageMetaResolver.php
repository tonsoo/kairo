<?php

declare(strict_types=1);

namespace App\Support\Metadata;

use Illuminate\Http\Request;

class PageMetaResolver
{
    /**
     * @param  array<int, array{code: string, url: string}>  $localeOptions
     */
    public function resolve(Request $request, string $locale, string $currentUrl, array $localeOptions): PageMeta
    {
        $appName = (string) config('app.name', 'Laravel');
        $routeName = $request->route()?->getName();
        $title = $this->resolveTitle($routeName, $appName);
        $description = $this->resolveDescription($routeName);

        return new PageMeta(
            title: $title,
            description: $description,
            robots: 'index,follow,max-image-preview:large,max-snippet:-1,max-video-preview:-1',
            applicationName: $appName,
            canonical: $currentUrl,
            alternates: $this->alternateLinks($localeOptions),
            openGraph: new OpenGraphMeta(
                type: 'website',
                siteName: $appName,
                title: $title,
                description: $description,
                url: $currentUrl,
                locale: $this->toOpenGraphLocale($locale),
                alternateLocales: $this->alternateLocales($locale, $localeOptions),
            ),
            twitter: new TwitterMeta(
                card: 'summary',
                title: $title,
                description: $description,
            ),
            structuredData: $this->resolveStructuredData(
                routeName: $routeName,
                appName: $appName,
                locale: $locale,
                currentUrl: $currentUrl,
                description: $description,
            ),
        );
    }

    private function resolveTitle(?string $routeName, string $appName): string
    {
        return match ($routeName) {
            'home' => __('home.meta.title'),
            'dashboard' => __('dashboard.page.title'),
            'history' => __('history.page.title'),
            'weekly-schedule' => __('weekly_schedule.title'),
            'profile.edit' => __('settings.profile.page_title'),
            'security.edit' => __('settings.security.page_title'),
            'app-settings.edit' => __('settings.app.page_title'),
            'appearance.edit' => __('settings.appearance.page_title'),
            'login' => __('auth.login.meta.title'),
            'register' => __('auth.register.meta.title'),
            'password.request' => __('auth.password_request.meta.title'),
            'password.reset' => __('auth.password_reset.meta.title'),
            'verification.notice' => __('auth.verification_notice.meta.title'),
            'password.confirm' => __('auth.password_confirm.meta.title'),
            'two-factor.login' => __('auth.two_factor.meta.title'),
            default => $appName,
        };
    }

    private function resolveDescription(?string $routeName): ?string
    {
        /**
         * @var ?string $match
         */
        $match = match ($routeName) {
            'home' => __('home.meta.description'),
            'login' => __('auth.login.meta.description'),
            'register' => __('auth.register.meta.description'),
            'password.request' => __('auth.password_request.meta.description'),
            'password.reset' => __('auth.password_reset.meta.description'),
            'verification.notice' => __('auth.verification_notice.meta.description'),
            'password.confirm' => __('auth.password_confirm.meta.description'),
            default => null,
        };

        return $match;
    }

    /**
     * @param  array<int, array{code: string, url: string}>  $localeOptions
     * @return array<int, AlternateLink>
     */
    private function alternateLinks(array $localeOptions): array
    {
        $alternateLinks = [];

        foreach ($localeOptions as $option) {
            $alternateLinks[] = new AlternateLink(
                locale: $option['code'],
                url: $option['url'],
            );
        }

        return $alternateLinks;
    }

    /**
     * @param  array<int, array{code: string, url: string}>  $localeOptions
     * @return array<int, string>
     */
    private function alternateLocales(string $locale, array $localeOptions): array
    {
        $alternateLocales = [];

        foreach ($localeOptions as $option) {
            if ($option['code'] === $locale) {
                continue;
            }

            $alternateLocales[] = $this->toOpenGraphLocale($option['code']);
        }

        return $alternateLocales;
    }

    private function toOpenGraphLocale(string $locale): string
    {
        return str_replace('-', '_', $locale);
    }

    private function resolveStructuredData(
        ?string $routeName,
        string $appName,
        string $locale,
        string $currentUrl,
        ?string $description,
    ): ?string {
        if ($routeName !== 'home' || $description === null) {
            return null;
        }

        $structuredData = json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'SoftwareApplication',
            'name' => $appName,
            'applicationCategory' => 'BusinessApplication',
            'operatingSystem' => 'Web',
            'url' => $currentUrl,
            'inLanguage' => $locale,
            'description' => $description,
            'offers' => [
                '@type' => 'Offer',
                'price' => '0',
                'priceCurrency' => 'USD',
            ],
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return is_string($structuredData) ? $structuredData : null;
    }
}

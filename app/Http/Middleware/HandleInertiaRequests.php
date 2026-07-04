<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\Localization\LocalizedUrlGenerator;
use App\Support\Metadata\PageMetaResolver;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function __construct(
        private LocalizedUrlGenerator $localizedUrlGenerator,
        private PageMetaResolver $pageMetaResolver,
    ) {}

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $locale = app()->currentLocale();
        $localeOptions = $this->localizedUrlGenerator->localeOptions($request);
        $currentUrl = $this->localizedUrlGenerator->currentUrl($request);

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'locale' => $locale,
            'localeOptions' => $localeOptions,
            'routeDefaults' => $this->localizedUrlGenerator->routeDefaults($locale),
            'currentUrl' => $currentUrl,
            'meta' => $this->pageMetaResolver->resolve(
                request: $request,
                locale: $locale,
                currentUrl: $currentUrl,
                localeOptions: $localeOptions,
            ),
            'auth' => [
                'user' => $request->user(),
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
        ];
    }
}

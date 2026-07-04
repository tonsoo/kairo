<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\Localization\LocalizedUrlGenerator;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function __construct(private LocalizedUrlGenerator $localizedUrlGenerator) {}

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

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'locale' => $locale,
            'localeOptions' => $this->localizedUrlGenerator->localeOptions($request),
            'routeDefaults' => $this->localizedUrlGenerator->routeDefaults($locale),
            'currentUrl' => $this->localizedUrlGenerator->currentUrl($request),
            'auth' => [
                'user' => $request->user(),
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
        ];
    }
}

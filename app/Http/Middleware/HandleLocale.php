<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\Localization\LocaleRouting;
use App\Support\Localization\LocalizedUrlGenerator;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

final class HandleLocale
{
    public function __construct(
        private LocaleRouting $localeRouting,
        private LocalizedUrlGenerator $localizedUrlGenerator,
    ) {}

    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $this->localeRouting->resolveLocale($request);

        app()->setLocale($locale);

        URL::defaults($this->localizedUrlGenerator->routeDefaults($locale));

        config()->set('fortify.home', $this->localizedUrlGenerator->url('dashboard', $locale, absolute: false));
        config()->set('fortify.redirects.email-verification', $this->localizedUrlGenerator->url('dashboard', $locale, absolute: false));

        return $next($request);
    }
}

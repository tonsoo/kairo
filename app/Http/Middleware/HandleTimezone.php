<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class HandleTimezone
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $timezone = $this->resolveTimezone($request);

        $request->attributes->set('timezone', $timezone);

        return $next($request);
    }

    private function resolveTimezone(Request $request): string
    {
        $timezone = $request->header('X-Timezone')
            ?? $request->input('timezone')
            ?? $request->cookie('timezone')
            ?? $this->resolveUserTimezone($request)
            ?? config('app.timezone');

        if (! is_string($timezone) || ! in_array($timezone, timezone_identifiers_list(), true)) {
            return config('app.timezone');
        }

        return $timezone;
    }

    private function resolveUserTimezone(Request $request): ?string
    {
        $user = $request->user();

        if (! $user instanceof User) {
            return null;
        }

        return $user->timezone;
    }
}

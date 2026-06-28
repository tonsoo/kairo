<?php

declare(strict_types=1);

namespace App\Providers;

use App\Enums\RateLimiterType;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

final class RateLimitServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->configureRateLimiters();
    }

    private function configureRateLimiters(): void
    {
        $this->forUserOrIp(RateLimiterType::read, 250);
        $this->forUserOrIp(RateLimiterType::write, 150);
    }

    private function forUserOrIp(RateLimiterType $type, int $perMinute): void
    {
        RateLimiter::for($type->value, function (Request $request) use ($perMinute) {
            $key = $this->getByKey($request);

            return Limit::perMinute($perMinute)
                ->by($key)
                ->response(fn (Request $request, array $headers) => response()->json([
                    'message' => 'Too many requests.',
                ], 429, $headers));
        });
    }

    private function getByKey(Request $request): string
    {
        $userId = $request->user()?->id;
        if ($userId !== null) {
            return "user_id:.{$userId}";
        }

        return "ip:{$request->ip()}";
    }
}

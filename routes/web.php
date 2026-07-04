<?php

use App\Enums\RateLimiterType;
use App\Http\Controllers\Panel\DashboardController;
use App\Http\Controllers\Panel\HistoryController;
use App\Http\Controllers\Panel\ShiftExportController;
use App\Http\Controllers\SitemapController;
use App\Support\Localization\LocaleRouting;
use Illuminate\Support\Facades\Route;

Route::get('/sitemap.xml', SitemapController::class)
    ->name('sitemap');

$localeRouting = app(LocaleRouting::class);

$localeRouting->registerRoutes(function (): void {
    Route::inertia('/', 'Welcome')->name('home');

    require __DIR__.'/auth.php';
    require __DIR__.'/settings.php';

    Route::middleware(['auth', 'verified'])->group(function (): void {
        $readThrottle = 'throttle:'.RateLimiterType::read->value;

        Route::get('/dashboard', DashboardController::class)->name('dashboard');
        Route::get('/history', HistoryController::class)->name('history');
        Route::get('/exports/download', ShiftExportController::class)
            ->middleware($readThrottle)
            ->name('shift-exports.download');
        Route::inertia('/weekly-schedule', 'WeeklySchedule')->name('weekly-schedule');
    });
});

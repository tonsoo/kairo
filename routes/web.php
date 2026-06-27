<?php

use App\Enums\RateLimiterType;
use App\Http\Controllers\Panel\DashboardController;
use App\Http\Controllers\Panel\HistoryController;
use App\Http\Controllers\Panel\ShiftExportController;
use App\Http\Controllers\LocaleController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');
Route::patch('locale/{locale}', LocaleController::class)
    ->whereIn('locale', config('app.supported_locales'))
    ->name('locale.update');

Route::middleware(['auth', 'verified'])->group(function () {
    $readThrottle = 'throttle:'.RateLimiterType::read->value;

    Route::get('dashboard', DashboardController::class)->name('dashboard');
    Route::get('history', HistoryController::class)->name('history');
    Route::get('shift-exports/download', ShiftExportController::class)
        ->middleware($readThrottle)
        ->name('shift-exports.download');
    Route::inertia('weekly-schedule', 'WeeklySchedule')->name('weekly-schedule');
});

require __DIR__.'/settings.php';

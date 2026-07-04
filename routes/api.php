<?php

use App\Enums\RateLimiterType;
use App\Http\Controllers\Api\CurrentShiftActionsController;
use App\Http\Controllers\Api\CurrentShiftStateController;
use App\Http\Controllers\Api\DailyWorkScheduleController;
use App\Http\Controllers\Api\HoursSummaryController;
use App\Http\Controllers\Api\ShiftBreakController;
use App\Http\Controllers\Api\ShiftController;
use App\Http\Controllers\Api\WorkScheduleController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'verified'])
    ->prefix('me')
    ->group(function () {
        $readThrottle = 'throttle:'.RateLimiterType::read->value;
        $writeThrottle = 'throttle:'.RateLimiterType::write->value;

        Route::get('hours-summary', HoursSummaryController::class)
            ->middleware($readThrottle)
            ->name('api.me.hours-summary');

        Route::get('current-shift-state', CurrentShiftStateController::class)
            ->middleware($readThrottle)
            ->name('api.me.current-shift-state');

        Route::get('shifts', [ShiftController::class, 'index'])
            ->middleware($readThrottle)
            ->name('api.me.shifts.index');

        Route::post('shifts', [ShiftController::class, 'store'])
            ->middleware($writeThrottle)
            ->block()
            ->name('api.me.shifts.store');

        Route::post('shifts/start', [CurrentShiftActionsController::class, 'start'])
            ->middleware($writeThrottle)
            ->block()
            ->name('api.me.shifts.start');

        Route::post('shifts/end', [CurrentShiftActionsController::class, 'end'])
            ->middleware($writeThrottle)
            ->block()
            ->name('api.me.shifts.end');

        Route::post('shifts/continue', [CurrentShiftActionsController::class, 'resume'])
            ->middleware($writeThrottle)
            ->block()
            ->name('api.me.shifts.continue');

        Route::post('shifts/remove-break', [ShiftBreakController::class, 'destroy'])
            ->middleware($writeThrottle)
            ->block()
            ->name('api.me.shifts.remove-break');

        Route::patch('shifts/{shift}', [ShiftController::class, 'update'])
            ->middleware($writeThrottle)
            ->block()
            ->name('api.me.shifts.update');

        Route::delete('shifts/{shift}', [ShiftController::class, 'destroy'])
            ->middleware($writeThrottle)
            ->block()
            ->name('api.me.shifts.destroy');

        Route::get('work-schedules', [WorkScheduleController::class, 'index'])
            ->middleware($readThrottle)
            ->name('api.me.work-schedules.index');

        Route::put('work-schedules', [WorkScheduleController::class, 'replace'])
            ->middleware($writeThrottle)
            ->block()
            ->name('api.me.work-schedules.replace');

        Route::get('daily-work-schedules/{date}', [DailyWorkScheduleController::class, 'show'])
            ->middleware($readThrottle)
            ->name('api.me.daily-work-schedules.show');

        Route::put('daily-work-schedules/{date}', [DailyWorkScheduleController::class, 'upsert'])
            ->middleware($writeThrottle)
            ->block()
            ->name('api.me.daily-work-schedules.upsert');
    });

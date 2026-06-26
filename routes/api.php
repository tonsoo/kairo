<?php

use App\Enums\RateLimiterType;
use App\Http\Controllers\Api\CurrentShiftStateController;
use App\Http\Controllers\Api\ShiftController;
use App\Http\Controllers\Api\WorkScheduleController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])
    ->prefix('me')
    ->group(function () {
        $readThrottle = 'throttle:'.RateLimiterType::read->value;
        $writeThrottle = 'throttle:'.RateLimiterType::write->value;

        Route::get('current-shift-state', CurrentShiftStateController::class)
            ->middleware($readThrottle)
            ->name('api.me.current-shift-state');

        Route::get('shifts', [ShiftController::class, 'index'])
            ->middleware($readThrottle)
            ->name('api.me.shifts.index');

        Route::post('shifts/start', [ShiftController::class, 'start'])
            ->middleware($writeThrottle)
            ->block()
            ->name('api.me.shifts.start');

        Route::post('shifts/end', [ShiftController::class, 'end'])
            ->middleware($writeThrottle)
            ->block()
            ->name('api.me.shifts.end');

        Route::post('shifts/continue', [ShiftController::class, 'continue'])
            ->middleware($writeThrottle)
            ->block()
            ->name('api.me.shifts.continue');

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
    });

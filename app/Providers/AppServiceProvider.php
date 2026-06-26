<?php

namespace App\Providers;

use App\Models\User;
use App\Observers\UserObserver;
use App\Repositories\CsvShiftExportRepository;
use App\Repositories\PdfShiftExportRepository;
use App\Repositories\ShiftExportRegistry;
use App\Repositories\XlsxShiftExportRepository;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(CsvShiftExportRepository::class);
        $this->app->singleton(XlsxShiftExportRepository::class);
        $this->app->singleton(PdfShiftExportRepository::class);
        $this->app->singleton(ShiftExportRegistry::class, function ($app): ShiftExportRegistry {
            return new ShiftExportRegistry(
                $app->make(CsvShiftExportRepository::class),
                $app->make(XlsxShiftExportRepository::class),
                $app->make(PdfShiftExportRepository::class),
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        User::observe(UserObserver::class);

        $this->configureDefaults();
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}

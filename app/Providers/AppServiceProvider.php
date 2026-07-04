<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\User;
use App\Observers\UserObserver;
use App\Repositories\ShiftExport\CsvShiftExportRepository;
use App\Repositories\ShiftExport\PdfShiftExportRepository;
use App\Repositories\ShiftExport\ShiftExportRegistry;
use App\Repositories\ShiftExport\XlsxShiftExportRepository;
use App\Support\Localization\LocaleRouting;
use App\Support\Localization\LocalizedUrlGenerator;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(CsvShiftExportRepository::class);
        $this->app->singleton(XlsxShiftExportRepository::class);
        $this->app->singleton(PdfShiftExportRepository::class);
        $this->app->singleton(LocaleRouting::class);
        $this->app->singleton(LocalizedUrlGenerator::class);
        $this->app->singleton(ShiftExportRegistry::class, function ($app): ShiftExportRegistry {
            return new ShiftExportRegistry(
                $app->make(CsvShiftExportRepository::class),
                $app->make(XlsxShiftExportRepository::class),
                $app->make(PdfShiftExportRepository::class),
            );
        });
    }

    public function boot(): void
    {
        User::observe(UserObserver::class);

        $this->configureDefaults();
    }

    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        $localeRouting = $this->app->make(LocaleRouting::class);
        $localizedUrlGenerator = $this->app->make(LocalizedUrlGenerator::class);

        URL::defaults($localizedUrlGenerator->routeDefaults($localeRouting->defaultLocale()));

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

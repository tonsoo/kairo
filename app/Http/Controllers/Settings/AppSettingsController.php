<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\AppSettingsUpdateRequest;
use DateTimeZone;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class AppSettingsController extends Controller
{
    public function edit(Request $request): Response
    {
        return Inertia::render('settings/App', [
            'timezone' => $request->user()->timezone,
            'timezones' => collect(DateTimeZone::listIdentifiers())
                ->sort()
                ->values()
                ->all(),
        ]);
    }

    public function update(AppSettingsUpdateRequest $request): RedirectResponse
    {
        $request->user()->forceFill($request->validated())->save();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('App settings updated.')]);

        return to_route('app-settings.edit');
    }
}

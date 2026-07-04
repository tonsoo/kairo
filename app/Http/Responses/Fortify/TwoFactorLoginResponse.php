<?php

namespace App\Http\Responses\Fortify;

use Illuminate\Http\Request;
use Laravel\Fortify\Contracts\TwoFactorLoginResponse as TwoFactorLoginResponseContract;

final class TwoFactorLoginResponse implements TwoFactorLoginResponseContract
{
    /**
     * @param  Request  $request
     */
    public function toResponse($request)
    {
        return $request->wantsJson()
            ? response()->json(['two_factor' => false])
            : redirect()->intended(route('dashboard', absolute: false));
    }
}

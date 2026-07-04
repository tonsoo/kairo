<?php

namespace App\Http\Responses\Fortify;

use Illuminate\Http\Request;
use Laravel\Fortify\Contracts\PasswordConfirmedResponse as PasswordConfirmedResponseContract;

final class PasswordConfirmedResponse implements PasswordConfirmedResponseContract
{
    /**
     * @param  Request  $request
     */
    public function toResponse($request)
    {
        return $request->wantsJson()
            ? response()->json(['confirmed' => true])
            : redirect()->intended(route('dashboard', absolute: false));
    }
}

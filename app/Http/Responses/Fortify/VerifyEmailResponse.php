<?php

namespace App\Http\Responses\Fortify;

use Illuminate\Http\Request;
use Laravel\Fortify\Contracts\VerifyEmailResponse as VerifyEmailResponseContract;

final class VerifyEmailResponse implements VerifyEmailResponseContract
{
    /**
     * @param  Request  $request
     */
    public function toResponse($request)
    {
        return $request->wantsJson()
            ? response()->json(['verified' => true])
            : redirect()->intended(route('dashboard', absolute: false).'?verified=1');
    }
}

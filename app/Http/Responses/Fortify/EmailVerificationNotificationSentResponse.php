<?php

namespace App\Http\Responses\Fortify;

use Illuminate\Http\Request;
use Laravel\Fortify\Contracts\EmailVerificationNotificationSentResponse as EmailVerificationNotificationSentResponseContract;
use Laravel\Fortify\Fortify;

final class EmailVerificationNotificationSentResponse implements EmailVerificationNotificationSentResponseContract
{
    /**
     * @param  Request  $request
     */
    public function toResponse($request)
    {
        return $request->wantsJson()
            ? response()->json('', 202)
            : redirect()->route('home')->with('status', Fortify::VERIFICATION_LINK_SENT);
    }
}

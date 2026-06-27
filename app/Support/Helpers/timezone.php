<?php

if (! function_exists('currentTimezone')) {
    function currentTimezone(): string
    {
        return request()->attributes->get('timezone', config('app.timezone'));
    }
}

<?php

namespace App\Enums;

enum RateLimiterType: string
{
    case read = 'read';
    case write = 'write';
}

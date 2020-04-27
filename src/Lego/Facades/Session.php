<?php

namespace JA\Lego\Facades;

use Illuminate\Support\Facades\Facade;

class Session extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'ja-lego-session';
    }
}

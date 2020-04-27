<?php

namespace JA\Lego\Facades;

use Illuminate\Support\Facades\Facade;

class Asset extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'ja-lego-asset';
    }
}

<?php

namespace JA\Lego\Facades;

use Illuminate\Support\Facades\Facade;

class Cache extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'ja-lego-cache';
    }
}

<?php

namespace JA\Lego\Foundation;

class Cache
{
    public function __call($method, $parameters)
    {
        if ($parameters && is_string($parameters[0])) {
            $parameters[0] = config('ja-lego.cache.key-prefix') . $parameters[0];
        }

        return call_user_func_array([\Illuminate\Support\Facades\Cache::class, $method], $parameters);
    }
}

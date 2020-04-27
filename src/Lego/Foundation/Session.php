<?php

namespace JA\Lego\Foundation;

class Session
{
    public function __call($method, $parameters)
    {
        $methods = ['exists', 'has', 'get', 'pull', 'put', 'remove', 'forget', 'push'];
        $sessionKeyPrefix = config('ja-lego.session.key-prefix');
        if (in_array($method, $methods) && !empty($parameters[0])) {
            $key = $parameters[0];
            if (is_array($key)) {
                foreach ($key as $keyIndex => $keyValue) {
                    $key[$keyIndex] = $sessionKeyPrefix . $keyValue;
                }
            } else {
                $key = $sessionKeyPrefix . $key;
            }

            $parameters[0] = $key;
        }

        return call_user_func_array([\Illuminate\Support\Facades\Session::class, $method], $parameters);
    }
}

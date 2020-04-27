<?php

namespace JA\Lego\Foundation;

class Arr
{
    public static function notNullValue()
    {
        foreach (func_get_args() as $value) {
            if (!is_null($val = value($value))) {
                return $val;
            }
        }

        return null;
    }
}

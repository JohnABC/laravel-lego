<?php

namespace JA\Lego\Foundation;

class Str
{
    public static function isEmpty($string)
    {
        return strlen(trim($string)) === 0;
    }
}
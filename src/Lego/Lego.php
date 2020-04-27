<?php

namespace JA\Lego;

use JA\Lego\Widget\Filter;

class Lego
{
    public const VERSION = '0.0.1';

    public static function filter($source)
    {
        return new Filter($source);
    }
}
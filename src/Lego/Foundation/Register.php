<?php

namespace JA\Lego\Foundation;

use Illuminate\Support\Arr;

class Register
{
    const TYPE_PRIORITY_RESPONSE = 'priorityResponse';
    const TYPE_FIELD_VALIDATOR = 'fieldValidator';

    const TAG_DEFAULT = 'default';

    protected static $registed = [];

    public static function set($type, $value, $tag = self::TAG_DEFAULT)
    {
        Arr::set(self::$registed, "{$type}.{$tag}", $value);
    }

    public static function push($type, $value, $tag = self::TAG_DEFAULT)
    {
        $values = static::get($type, $tag);
        $values = is_null($values) ? [] : $values;
        $values[] = $value;

        Arr::set(self::$registed, "{$type}.{$tag}", $values);
    }

    public static function get($type, $tag = self::TAG_DEFAULT)
    {
        $key = $tag ? "{$type}.{$tag}" : $type;

        return Arr::get(self::$registed, $key);
    }
}
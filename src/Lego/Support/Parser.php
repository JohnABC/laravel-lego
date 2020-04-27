<?php

namespace JA\Lego\Support;

class Parser
{
    const ATTRIBUTE_PATH_JSON_DELIMITER = ':';
    const ATTRIBUTE_PATH_RELATION_DELIMITER = '.';

    public static function splitAttributePath($attribute)
    {
        $parts = explode(static::ATTRIBUTE_PATH_JSON_DELIMITER, $attribute, 2);
        $jsonPath = count($parts) === 2 ? explode(static::ATTRIBUTE_PATH_JSON_DELIMITER, end($parts)) : [];

        $relationParts = explode(static::ATTRIBUTE_PATH_RELATION_DELIMITER, $parts[0]);

        return [
            array_slice($relationParts, 0, -1),
            end($relationParts),
            $jsonPath,
        ];
    }

    public static function flattenAttributePath($attribute)
    {
        return str_replace([static::ATTRIBUTE_PATH_JSON_DELIMITER, static::ATTRIBUTE_PATH_RELATION_DELIMITER], '_', $attribute);
    }
}
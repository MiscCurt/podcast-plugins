<?php

class ArrayHelper
{
    public static function getDate($key, $array)
    {
        $value = self::getValue($key, $array);
        return $value ? strtotime($value) : null;
    }

    public static function getValue($key, $array)
    {
        if (is_array($array)) {
            return array_key_exists($key, $array) ? $array[$key] : null;
        } else {
            return null;
        }
    }
}

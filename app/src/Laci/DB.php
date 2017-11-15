<?php

namespace TriTan\Laci;

class DB
{

    protected static $collections = [];

    public static function open($file, array $options = array())
    {
        if (!isset(static::$collections[$file])) {
            static::$collections[$file] = new Collection($file, $options);
        }

        return static::$collections[$file];
    }

}

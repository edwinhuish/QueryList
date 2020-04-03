<?php

namespace QL\Statics;

/**
 * Class QueryList
 * @package QL\Statics
 *
 * @method \QL\QueryList load( string $html_string )
 *
 */
class QueryList
{

    public static function newInstance()
    {
        return new QueryList();
    }

    public static function __callStatic($name, $arguments)
    {
        return static::newInstance()->$name(...$arguments);
    }
}
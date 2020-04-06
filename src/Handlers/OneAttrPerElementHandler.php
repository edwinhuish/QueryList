<?php

namespace QL\Handlers;

use QL\Contracts\HandleAttributesContract;

class OneAttrPerElementHandler implements HandleAttributesContract
{
    /**
     * @param  array  $attr_array
     * @param  array|object  $rule
     * @param  string|null  $range
     * @param  mixed  ...$args
     * @return array|string|null
     */
    public static function handle(array $attr_array, $rule, $range, ...$args)
    {
        return array_shift($attr_array);
    }
}

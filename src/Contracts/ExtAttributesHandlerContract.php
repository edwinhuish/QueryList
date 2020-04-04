<?php

namespace QL\Contracts;

interface ExtAttributesHandlerContract
{
    /**
     * @param  array  $attr_array
     * @param  array|object  $rule
     * @param  string|null  $range
     * @param  mixed  ...$args
     * @return array|string|null
     */
    public function handle(array $attr_array, $rule, $range, ...$args);

}

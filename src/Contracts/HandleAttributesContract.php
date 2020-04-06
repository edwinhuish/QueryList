<?php

namespace QL\Contracts;

interface HandleAttributesContract
{
    /**
     * This function just work for the first one in case of the return type can be any!!
     *
     * 由于返回类型可以是任意类型，此函数仅会执行第一个加入的 handler ！！
     *
     * @param  array  $attr_array
     * @param  array|object  $rule
     * @param  string|null  $range
     * @param  mixed  ...$args
     * @return array|string|null
     */
    public static function handle(array $attr_array, $rule, $range, ...$args);

}

<?php

namespace QL\Contracts;

interface HandleAttributeContract
{
    /**
     * @param  string  $attr
     * @param  array|object  $rule
     * @param  mixed  ...$args
     * @return string
     */
    public function handle(string $attr, $rule, ...$args): string;

}

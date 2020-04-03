<?php

namespace QL\Contracts;

interface ResultHandlerContract
{
    /**
     * @param  array  $result
     * @param  array|object  $rule
     * @param  mixed  ...$args
     * @return array
     */
    public function handle(array $result, $rule, ...$args): array;

}

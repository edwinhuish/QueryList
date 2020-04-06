<?php

namespace QL\Contracts;

interface HandleHtmlContract
{
    /**
     * @param  string  $html
     * @param  mixed  ...$args
     * @return string
     */
    public static function handle(string $html, ...$args): string;

}

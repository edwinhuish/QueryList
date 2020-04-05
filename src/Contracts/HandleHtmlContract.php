<?php

namespace QL\Contracts;

interface HandleHtmlContract
{
    /**
     * @param  string  $html
     * @param  mixed  ...$args
     * @return string
     */
    public function handle(string $html, ...$args): string;

}

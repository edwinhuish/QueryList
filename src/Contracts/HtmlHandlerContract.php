<?php

namespace QL\Contracts;

interface HtmlHandlerContract
{
    /**
     * @param  string  $html
     * @param  mixed  ...$args
     * @return string
     */
    public function handle(string $html, ...$args): string;

}

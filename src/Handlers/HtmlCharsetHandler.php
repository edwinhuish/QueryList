<?php

namespace QL\Handlers;

use QL\Contracts\HandleHtmlContract;

class HtmlCharsetHandler implements HandleHtmlContract
{
    /**
     * @param  string  $html
     * @param  mixed  ...$args
     * @return string
     */
    public function handle(string $html, ...$args): string
    {
        preg_match('/<meta[^>]+charset=[\'"]?([^"\';\s]*)[\'"]?[^>]*>/', $html, $matches);

        $charset = strtoupper($matches[1] ?? '');

        if ('UTF-8' !== $charset && 'UTF8' !== $charset) {
            $html = empty($charset) ? mb_convert_encoding($html, "UTF-8") : iconv($charset, 'UTF-8//IGNORE', $html);
        }

        // remove charset meta
        $html = preg_replace('/<meta[^>]+charset=[\'"]?([^"\';\s]*)[\'"]?[^>]*>/', '', $html);

        // add UTF-8 charset meta to next <head>
        $html = str_replace(/** @lang text */ '<head>', /** @lang text */ '<head><meta http-equiv="Content-Type" content="text/html;charset=UTF-8">', $html);

        return $html;
    }
}

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

        $charset = $matches[1] ?: 'auto';

        $charset = strtoupper($charset);
        if ('UTF-8' === $charset || 'UTF8' === $charset) {
            return $html;
        }

        $newHtml = mb_convert_encoding($html, "UTF-8", $charset);

        return $charset === 'auto' ? $newHtml : preg_replace('/(<meta[^>]+charset=[\'"]?)([^"\';\s]*)([\'"]?[^>]*>)/', '${1}UTF-8${2}', $newHtml);
    }
}

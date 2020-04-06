<?php

namespace QL\Handlers;

use QL\Contracts\HandleHtmlContract;

class HtmlCharsetHandler implements HandleHtmlContract
{
    /**
     * @param  string  $html
     * @param  string|null  $from
     * @param  string|null  $to
     * @param  mixed  ...$args
     * @return string
     */
    public static function handle(string $html, $from = null, $to = 'UTF-8', ...$args): string
    {
        if ( ! $from) {
            preg_match('/<meta[^>]+charset=[\'"]?([^"\';\s]*)[\'"]?[^>]*>/', $html, $matches);

            $from = $matches[1] ?? '';
        }
        $from = $from ?: self::detect($html);

        $from = strtoupper($from);
        $to   = strtoupper($to);

        if ($from === $to) {
            return $html;
        }

        $newHtml = iconv($from, $to.'//IGNORE', $html);

        if (false === $newHtml) {
            $newHtml = mb_convert_encoding($html, $to, $from);
        }

        // remove charset meta
        $newHtml = preg_replace('/<meta[^>]+charset=[\'"]?([^"\';\s]*)[\'"]?[^>]*>/', '', $newHtml);

        if (strpos($newHtml, /** @lang text */ '<head>') !== false) {
            // add UTF-8 charset meta to next <head>
            $newHtml = str_replace(
            /** @lang text */
                '<head>',
                /** @lang text */
                '<head><meta http-equiv="Content-Type" content="text/html;charset='.$to.'">',
                $newHtml
            );
        }

        return $newHtml;
    }

    /**
     * @param $string
     * @return string
     */
    public static function detect($string)
    {
        $charset = mb_detect_encoding($string, array('ASCII', 'GB2312', 'GBK', 'UTF-8'), true);
        if (strtolower($charset) == 'cp936') {
            $charset = 'GBK';
        }
        return $charset;
    }
}

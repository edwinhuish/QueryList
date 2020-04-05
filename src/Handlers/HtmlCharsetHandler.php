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

        $charset = $args[0] ?? null;

        $charset = $charset ?: ($matches[1] ?? '');

        $charset = strtoupper($charset);

        $newHtml = $html;
        if ('UTF-8' !== $charset && 'UTF8' !== $charset) {
            if (empty($charset)) {
                $charset = self::detect($html);
            }
            $newHtml = iconv($charset, 'UTF-8//IGNORE', $html);

            if (false === $newHtml) {
                $newHtml = mb_convert_encoding($html, 'UTF-8', $charset);
            }
        }

        // remove charset meta
        $newHtml = preg_replace('/<meta[^>]+charset=[\'"]?([^"\';\s]*)[\'"]?[^>]*>/', '', $newHtml);

        $newMeta = '<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">';

        if (strpos($newHtml, /** @lang text */ '<head>') !== false) {
            // add UTF-8 charset meta to next <head>
            $newHtml = str_replace(/** @lang text */ '<head>', /** @lang text */ '<head>'.$newMeta, $newHtml);
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

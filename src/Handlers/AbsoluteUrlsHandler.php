<?php

namespace QL\Handlers;

use phpUri;
use QL\Contracts\DocumentHandlerContract;
use QL\Dom\Document;
use QL\Dom\Elements;

class AbsoluteUrlsHandler implements DocumentHandlerContract
{

    /**
     * @param  Document  $document
     * @param  string  $currentUri
     * @param  array  $attrs
     * @param  mixed  ...$args
     * @return Document
     */
    public function handle(Document $document, $currentUri = '', $attrs = ['href', 'src'], ...$args): Document
    {
        if (empty($currentUri)) {
            return $document;
        }

        $parser = phpUri::parse($currentUri);

        foreach ($attrs as $attr) {
            $document->find('['.$attr.']')->each(function (Elements $element) use ($parser, $attr) {

                $relativeUrl = $element->attr($attr);

                if (substr($relativeUrl, 0, 11) === "javascript:") {
                    return;
                }

                $absoluteUrl = $parser->join($relativeUrl);
                $element->attr($attr, $absoluteUrl);

            });
        }

        return $document;
    }
}
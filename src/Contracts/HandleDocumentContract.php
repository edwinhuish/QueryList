<?php

namespace QL\Contracts;


use QL\Dom\Document;

interface HandleDocumentContract
{
    /**
     * @param  Document  $document
     * @param  mixed  ...$args
     * @return Document
     */
    public static function handle(Document $document, ...$args): Document;

}

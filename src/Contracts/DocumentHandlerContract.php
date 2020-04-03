<?php

namespace QL\Contracts;


use QL\Dom\Document;

interface DocumentHandlerContract
{
    /**
     * @param  Document  $document
     * @param  mixed  ...$args
     * @return Document
     */
    public function handle(Document $document, ...$args): Document;

}

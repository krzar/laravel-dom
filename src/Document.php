<?php

declare(strict_types=1);

namespace KrZar\LaravelDom;

use DOMDocument;

class Document extends DocumentNode
{
    public function __construct(private readonly DOMDocument $domDocument)
    {
        parent::__construct($this, $domDocument);
    }

    public static function create(string $version = '1.0', string $encoding = ''): Document
    {
        $domDocument = new DOMDocument($version, $encoding);

        return new self($domDocument);
    }

    public static function loadHtml(string $html, string $version = '1.0', string $encoding = ''): Document
    {
        $domDocument = new DOMDocument($version, $encoding);

        $useInternalErrors = libxml_use_internal_errors(true);
        $domDocument->loadHTML($html);
        libxml_use_internal_errors($useInternalErrors);

        return new self($domDocument);
    }

    public static function loadXml(string $xml, string $version = '1.0', string $encoding = ''): Document
    {
        $domDocument = new DOMDocument($version, $encoding);

        $useInternalErrors = libxml_use_internal_errors(true);
        $domDocument->loadXML($xml);
        libxml_use_internal_errors($useInternalErrors);

        return new self($domDocument);
    }

    public function append(DocumentElement $documentElement): void
    {
        $this->domDocument->append($documentElement->toNative());
    }

    public function prepend(DocumentElement $documentElement): void
    {
        $this->domDocument->prepend($documentElement->toNative());
    }

    public function toNative(): DOMDocument
    {
        return $this->domDocument;
    }
}

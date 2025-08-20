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

    public static function loadHtml(string $html, $version = '1.0', $encoding = ''): Document
    {
        $domDocument = new DOMDocument($version, $encoding);
        $domDocument->loadHTML($html);

        return new self($domDocument);
    }

    public static function loadXml(string $xml, $version = '1.0', $encoding = ''): Document
    {
        $domDocument = new DOMDocument($version, $encoding);
        $domDocument->loadXML($xml);

        return new self($domDocument);
    }

    public function toNative(): DOMDocument
    {
        return $this->domDocument;
    }
}

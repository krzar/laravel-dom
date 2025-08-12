<?php

declare(strict_types=1);

namespace KrZar\LaravelDom\Query;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMNodeList;
use DOMXPath;
use Illuminate\Support\Collection;
use KrZar\LaravelDom\Document;
use KrZar\LaravelDom\DocumentElement;
use KrZar\LaravelDom\DocumentNode;

class Builder
{
    private readonly DOMXPath $xPath;

    /**
     * @var Query[]
     */
    private array $queries = [];


    public function __construct(
        private readonly Document $document,
        private readonly ?DOMNode $domNode = null,
    ) {
        $this->xPath = new DOMXPath($document->toNative());
    }

    public function query(
        string $tag,
        \Closure $closure,
        bool $deep = false,
    ): static
    {
        $itemBuilder = new Query($tag, $deep);

        $closure($itemBuilder);

        $this->queries[] = $itemBuilder;

        return $this;
    }

    public function get(): Collection
    {
        $result = $this->queryResult();
        $collection = new Collection();

        if (!$result) {
            return $collection;
        }

        /** @var DOMElement|DOMNode $item */
        foreach ($result as $item) {
            if ($item instanceof DOMElement) {
                $collection->push(new DocumentElement($this->document, $item));

                continue;
            }

            $collection->push(new DocumentNode($this->document, $item));
        }

        return $collection;
    }

    public function first(): null|DocumentElement|DocumentNode
    {
        $result = $this->queryResult();

        if (!$result) {
            return null;
        }

        $item = $result->item(0);

        if ($item instanceof DOMElement) {
            return new DocumentElement($this->document, $item);
        }

        return new DocumentNode($this->document, $item);
    }

    private function queryResult(): DOMNodeList|false
    {
        $query = $this->queryString();

        $this->queries = [];

        return $this->xPath->query($query, $this->domNode);
    }

    public function queryString(): string
    {
        $query = '.';

        foreach ($this->queries as $queryItem) {
            $query .= $queryItem->toQueryString();
        }

        return $query;
    }
}
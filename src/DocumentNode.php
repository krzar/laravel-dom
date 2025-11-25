<?php

declare(strict_types=1);

namespace KrZar\LaravelDom;

use DOMDocument;
use DOMElement;
use DOMNode;
use Illuminate\Support\Collection;
use KrZar\LaravelDom\Query\Builder;

class DocumentNode
{
    private readonly Builder $builder;

    public function __construct(
        private readonly Document $document,
        private readonly DOMNode $domNode,
    ) {
        $this->builder = new Builder($document, $domNode);
    }

    public function text(): string
    {
        return trim($this->domNode->textContent);
    }

    public function setText(string $text): void
    {
        $this->domNode->textContent = $text;
    }

    public function attribute(string $name, mixed $default = null): mixed
    {
        return $this->domNode->attributes->getNamedItem($name)->textContent ?? $default;
    }

    public function html(): ?string
    {
        $this->document->toNative()->formatOutput = true;

        $html = $this->document->toNative()->saveHTML($this->domNode);

        return $html !== false ? $html : null;
    }

    public function xml(): ?string
    {
        $this->document->toNative()->formatOutput = true;

        $xml = $this->document->toNative()->saveXML($this->domNode);

        return $xml !== false ? $xml : null;
    }

    public function toNative(): DOMNode
    {
        return $this->domNode;
    }

    public function document(): Document
    {
        return $this->document;
    }

    /**
     * @return Collection<int, DocumentElement|DocumentNode>
     */
    public function children(): Collection
    {
        $children = new Collection;

        foreach ($this->domNode->childNodes as $childNode) {
            if ($childNode instanceof DOMElement) {
                $children->push(new DocumentElement($this->document, $childNode));

                continue;
            }

            $children->push(new DocumentNode($this->document, $childNode));
        }

        return $children;
    }

    public function parent(): ?DocumentNode
    {
        $parent = $this->domNode->parentNode;

        return $parent ? new DocumentNode($this->document, $parent) : null;
    }

    public function previousSibling(): ?DocumentNode
    {
        $sibling = $this->domNode->previousSibling;

        return $sibling ? new DocumentNode($this->document, $sibling) : null;
    }

    public function nextSibling(): ?DocumentNode
    {
        $sibling = $this->domNode->nextSibling;

        return $sibling ? new DocumentNode($this->document, $sibling) : null;
    }

    public function query(
        string $tag,
        \Closure $closure,
        bool $deep = false,
    ): Builder {
        return $this->builder->query($tag, $closure, $deep);
    }

    public function queryDeep(string $tag, \Closure $closure): Builder
    {
        return $this->builder->queryDeep($tag, $closure);
    }

    protected function importNode(DocumentNode $documentNode): DOMNode
    {
        $node = $documentNode->toNative();

        /** @var DOMDocument $targetDocument */
        $targetDocument = $this->domNode->ownerDocument ?? $this->domNode;

        if ($node->ownerDocument !== $targetDocument) {
            return $targetDocument->importNode($node, true);
        }

        return $node;
    }
}

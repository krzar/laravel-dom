<?php

declare(strict_types=1);

namespace KrZar\LaravelDom;

use DOMElement;
use Illuminate\Support\Collection;

class DocumentElement extends DocumentNode
{
    public function __construct(
        private readonly Document $document,
        private readonly DOMElement $domElement,
    ) {
        parent::__construct($document, $domElement);
    }

    public function id(): ?string
    {
        return $this->attribute('id');
    }

    public function className(): ?string
    {
        return $this->attribute('class');
    }

    /**
     * @return Collection<int, string>
     */
    public function classes(): Collection
    {
        $className = $this->className();

        if (empty($className)) {
            return collect();
        }

        return collect(explode(' ', $className));
    }

    public function tagName(): string
    {
        return $this->domElement->tagName;
    }

    public function parent(): null|DocumentElement|DocumentNode
    {
        $parent = $this->domElement->parentNode;

        if ($parent instanceof DOMElement) {
            return new DocumentElement($this->document, $parent);
        }

        return parent::parent();
    }

    /**
     * @return Collection<string, string>
     */
    public function attributes(): Collection
    {
        $nativeAttributes = $this->domElement->attributes;

        $attributes = [];

        for ($i = 0; $i < $nativeAttributes->length; $i++) {
            $nativeAttribute = $nativeAttributes->item($i);

            if ($nativeAttribute === null) {
                continue;
            }

            $attributes[$nativeAttribute->nodeName] = $nativeAttribute->nodeValue;
        }

        return collect($attributes);
    }

    public function toNative(): DOMElement
    {
        return $this->domElement;
    }
}

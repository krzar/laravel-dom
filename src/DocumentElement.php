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

    public static function create(string $tag): DocumentElement
    {
        $document = Document::create();
        $domElement = new DOMElement($tag);

        return new self($document, $domElement);
    }

    public function id(): ?string
    {
        return $this->attribute('id');
    }

    public function setId(string $id): void
    {
        $this->setAttribute('id', $id);
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

    public function hasClass(string $className): bool
    {
        return $this->classes()->contains($className);
    }

    public function addClass(string $className): void
    {
        $classes = $this->classes();

        $classes->add($className);

        $this->setAttribute('class', $classes->implode(' '));
    }

    public function removeClass(string $className): void
    {
        $classes = $this->classes();

        $newClasses = $classes->filter(fn (string $class) => $class !== $className);

        $this->setAttribute('class', $newClasses->implode(' '));
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

    public function hasAttribute(string $name): bool
    {
        return $this->domElement->hasAttribute($name);
    }

    public function setAttribute(string $name, mixed $value): void
    {
        $this->domElement->setAttribute($name, (string) $value);
    }

    public function removeAttribute(string $name): void
    {
        $this->domElement->removeAttribute($name);
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

    public function append(DocumentNode $documentElement): void
    {
        $this->domElement->append($documentElement->toNative());
    }

    public function prepend(DocumentNode $documentElement): void
    {
        $this->domElement->prepend($documentElement->toNative());
    }

    public function remove(): void
    {
        $this->domElement->remove();
    }

    public function toNative(): DOMElement
    {
        return $this->domElement;
    }
}

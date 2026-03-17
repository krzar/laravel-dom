<?php

declare(strict_types=1);

namespace KrZar\LaravelDom\Query;

readonly class QueryItem
{
    public function __construct(
        private string $attribute,
        private Operator $operator = Operator::EQUALS,
        private ?string $value = null,
        public Connector $connector = Connector::AND,
    ) {}

    public function toQueryString(): string
    {
        $attribute = $this->getAttributeSelector();

        return match ($this->operator) {
            Operator::CONTAINS => sprintf('contains(%s, "%s")', $attribute, $this->value),
            Operator::HAS => sprintf('%s', $attribute),
            Operator::NOT_CONTAINS => sprintf('not(contains(%s, "%s"))', $attribute, $this->value),
            Operator::NOT_HAS => sprintf('not(%s)', $attribute),
            Operator::NOT_EQUALS => sprintf('not(%s = "%s")', $attribute, $this->value),
            Operator::EQUALS => sprintf('%s = "%s"', $attribute, $this->value),
        };
    }

    private function getAttributeSelector(): string
    {
        if (str_starts_with($this->attribute, 'normalize-space') || str_starts_with($this->attribute, '@')) {
            return $this->attribute;
        }

        return '@'.$this->attribute;
    }
}

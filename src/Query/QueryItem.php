<?php

declare(strict_types=1);

namespace KrZar\LaravelDom\Query;

readonly class QueryItem
{
    public function __construct(
        private string $attribute,
        private string $operator = '=',
        private ?string $value = null,
        public string $connector = 'and',
    ) {}

    public function toQueryString(): string
    {
        $attribute = $this->getAttributeSelector();

        return match ($this->operator) {
            'contains' => sprintf('contains(%s, "%s")', $attribute, $this->value),
            'has' => sprintf('%s', $attribute),
            '!contains' => sprintf('not(contains(%s, "%s"))', $attribute, $this->value),
            '!has' => sprintf('not(%s)', $attribute),
            '!=' => sprintf('not(%s = "%s")', $attribute, $this->value),
            default => sprintf('%s = "%s"', $attribute, $this->value)
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

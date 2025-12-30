<?php

declare(strict_types=1);

namespace KrZar\LaravelDom\Query;

use Closure;
use KrZar\LaravelDom\Exception\NullValueException;

class Query
{
    /** @var (QueryItem|Query)[] */
    private array $queryItems = [];

    public function __construct(
        private readonly string $tag,
        private readonly bool $isDeep,
        private readonly bool $isSubQuery = false,
    ) {}

    public function where(
        string|Closure $attribute,
        string|Operator $operator = Operator::EQUALS,
        ?string $value = null
    ): static {
        return $this->handle($attribute, $operator, $value);
    }

    public function whereEquals(string $attribute, string $value): static
    {
        return $this->where($attribute, Operator::EQUALS, $value);
    }

    public function whereNotEquals(string $attribute, string $value): static
    {
        return $this->where($attribute, Operator::NOT_EQUALS, $value);
    }

    public function whereContains(string $attribute, string $value): static
    {
        return $this->where($attribute, Operator::CONTAINS, $value);
    }

    public function whereNotContains(string $attribute, string $value): static
    {
        return $this->where($attribute, Operator::NOT_CONTAINS, $value);
    }

    public function whereHas(string $attribute): static
    {
        return $this->where($attribute, Operator::HAS);
    }

    public function whereNotHas(string $attribute): static
    {
        return $this->where($attribute, Operator::NOT_HAS);
    }

    public function orWhere(
        string|Closure $attribute,
        string|Operator $operator = Operator::EQUALS,
        ?string $value = null
    ): static {
        return $this->handle($attribute, $operator, $value, Connector::OR);
    }

    public function orWhereEquals(string $attribute, string $value): static
    {
        return $this->orWhere($attribute, Operator::EQUALS, $value);
    }

    public function orWhereNotEquals(string $attribute, string $value): static
    {
        return $this->orWhere($attribute, Operator::NOT_EQUALS, $value);
    }

    public function orWhereContains(string $attribute, string $value): static
    {
        return $this->orWhere($attribute, Operator::CONTAINS, $value);
    }

    public function orWhereNotContains(string $attribute, string $value): static
    {
        return $this->orWhere($attribute, Operator::NOT_CONTAINS, $value);
    }

    public function orWhereHas(string $attribute): static
    {
        return $this->orWhere($attribute, Operator::HAS);
    }

    public function orWhereNotHas(string $attribute): static
    {
        return $this->orWhere($attribute, Operator::NOT_HAS);
    }

    public function whereText(string|Operator $operator = Operator::EQUALS, ?string $value = null, bool $deep = false): static
    {
        return $this->handleText($operator, $value, $deep, Connector::AND);
    }

    public function whereTextEquals(string $value, bool $deep = false): static
    {
        return $this->whereText(Operator::EQUALS, $value, $deep);

    }

    public function whereTextNotEquals(string $value, bool $deep = false): static
    {
        return $this->whereText(Operator::NOT_EQUALS, $value, $deep);
    }

    public function whereTextContains(string $value, bool $deep = false): static
    {
        return $this->whereText(Operator::CONTAINS, $value, $deep);
    }

    public function whereTextNotContains(string $value, bool $deep = false): static
    {
        return $this->whereText(Operator::NOT_CONTAINS, $value, $deep);
    }

    public function orWhereText(string|Operator $operator = Operator::EQUALS, ?string $value = null, bool $deep = false): static
    {
        return $this->handleText($operator, $value, $deep, Connector::OR);
    }

    public function orWhereTextEquals(string $value, bool $deep = false): static
    {
        return $this->orWhereText(Operator::EQUALS, $value, $deep);
    }

    public function orWhereTextNotEquals(string $value, bool $deep = false): static
    {
        return $this->orWhereText(Operator::NOT_EQUALS, $value, $deep);
    }

    public function orWhereTextContains(string $value, bool $deep = false): static
    {
        return $this->orWhereText(Operator::CONTAINS, $value, $deep);
    }

    public function orWhereTextNotContains(string $value, bool $deep = false): static
    {
        return $this->orWhereText(Operator::NOT_CONTAINS, $value, $deep);
    }

    public function toQueryString(): string
    {
        if ($this->isSubQuery) {
            return sprintf('(%s)', $this->getQueryStringBody());
        }

        $base = $this->isDeep ? '//' : '/';
        $body = $this->getQueryStringBody();

        if (empty($body)) {
            return sprintf('%s%s', $base, $this->tag);
        }

        return sprintf('%s%s[%s]', $base, $this->tag, $body);
    }

    private function getQueryStringBody(): string
    {
        $query = '';

        foreach ($this->queryItems as $index => $queryItem) {
            if ($index === 0) {
                $query = $queryItem->toQueryString();

                continue;
            }

            $connector = $queryItem instanceof Query ? $queryItem->tag : $queryItem->connector->value;
            $query .= sprintf(' %s %s', $connector, $queryItem->toQueryString());
        }

        return $query;
    }

    private function handle(
        string|Closure $attribute,
        string|Operator $operator,
        ?string $value = null,
        Connector $connector = Connector::AND,
    ): static {
        if ($attribute instanceof Closure) {
            $subQuery = new Query($connector->value, $this->isDeep, true);

            $attribute($subQuery);

            $this->queryItems[] = $subQuery;

            return $this;
        }

        if (is_string($operator)) {
            $operator = Operator::tryFrom($operator) ?? $operator;
        }

        if ($value === null && is_string($operator)) {
            $value = $operator;
            $operator = Operator::EQUALS;
        }

        if ($value === null && $operator->isValueRequired()) {
            throw new NullValueException($operator);
        }

        $this->queryItems[] = new QueryItem($attribute, $operator, $value, $connector);

        return $this;
    }

    private function handleText(string|Operator $operator, ?string $value, bool $deep, Connector $connector): static
    {
        return $this->handle(
            $deep ? 'normalize-space(.)' : 'normalize-space(text())',
            $operator,
            $value,
            $connector,
        );
    }
}

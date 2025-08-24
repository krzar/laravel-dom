<?php

declare(strict_types=1);

namespace KrZar\LaravelDom\Query;

use Closure;

class Query
{
    /** @var (QueryItem|Query)[] */
    private array $queryItems = [];

    public function __construct(
        private readonly string $tag,
        private readonly bool $isDeep,
        private readonly bool $isSubQuery = false,
    ) {}

    public function where(string|Closure $attribute, string $operator = '=', ?string $value = null): static
    {
        return $this->handle($attribute, $operator, $value, 'and');
    }

    public function whereHas(string $attribute): static
    {
        return $this->handle($attribute, 'has', connector: 'and');
    }

    public function whereNotHas(string $attribute): static
    {
        return $this->handle($attribute, '!has', connector: 'and');
    }

    public function orWhere(string|Closure $attribute, string $operator = '=', ?string $value = null): static
    {
        return $this->handle($attribute, $operator, $value, 'or');
    }

    public function orWhereHas(string $attribute): static
    {
        return $this->handle($attribute, 'has', connector: 'or');
    }

    public function orWhereNotHas(string $attribute): static
    {
        return $this->handle($attribute, '!has', connector: 'or');
    }

    public function whereText(string $operator = '=', ?string $value = null, bool $deep = false): static
    {
        return $this->handleText($operator, $value, $deep, 'and');
    }

    public function orWhereText(string $operator = '=', ?string $value = null, bool $deep = false): static
    {
        return $this->handleText($operator, $value, $deep, 'or');
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

            $connector = $queryItem instanceof Query ? $queryItem->tag : $queryItem->connector;
            $query .= sprintf(' %s %s', $connector, $queryItem->toQueryString());
        }

        return $query;
    }

    private function handle(
        string|Closure $attribute,
        ?string $operator = null,
        ?string $value = null,
        ?string $connector = null,
    ): static {
        if ($attribute instanceof Closure) {
            $subQuery = new Query($connector, $this->isDeep, true);

            $attribute($subQuery);

            $this->queryItems[] = $subQuery;

            return $this;
        }

        if ($value === null && ! in_array($operator, ['has', '!has'])) {
            $value = $operator;
            $operator = '=';
        }

        $this->queryItems[] = new QueryItem($attribute, $operator, $value, $connector);

        return $this;
    }

    private function handleText(string $operator, ?string $value, bool $deep, string $connector): static
    {
        return $this->handle(
            $deep ? 'normalize-space(.)' : 'normalize-space(text())',
            $operator,
            $value,
            $connector,
        );
    }
}

<?php

declare(strict_types=1);

namespace KrZar\LaravelDom\Tests\Unit\Query;

use KrZar\LaravelDom\Query\QueryItem;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class QueryItemTest extends TestCase
{
    #[DataProvider('basicQueryItemProvider')]
    public function testBasicQueryItems(string $attribute, string $operator, ?string $value, string $connector, string $expected): void
    {
        $queryItem = new QueryItem($attribute, $operator, $value, $connector);

        $this->assertEquals($expected, $queryItem->toQueryString());
        $this->assertEquals($connector, $queryItem->connector);
    }

    #[DataProvider('attributeSelectorProvider')]
    public function testAttributeSelectors(string $attribute, string $operator, ?string $value, string $expected): void
    {
        $queryItem = new QueryItem($attribute, $operator, $value);

        $this->assertEquals($expected, $queryItem->toQueryString());
    }

    #[DataProvider('operatorProvider')]
    public function testOperators(string $operator, string $attribute, ?string $value, string $expected): void
    {
        $queryItem = new QueryItem($attribute, $operator, $value);

        $this->assertEquals($expected, $queryItem->toQueryString());
    }

    public function testSpecialAttributes(): void
    {
        $normalizeSpaceItem = new QueryItem('normalize-space(.)', '=', 'text');
        $this->assertEquals('normalize-space(.) = "text"', $normalizeSpaceItem->toQueryString());

        $atAttributeItem = new QueryItem('@custom-attr', '=', 'value');
        $this->assertEquals('@custom-attr = "value"', $atAttributeItem->toQueryString());
    }

    public static function basicQueryItemProvider(): \Generator
    {
        yield 'class equals with and' => ['class', '=', 'container', 'and', '@class = "container"'];
        yield 'id equals with or' => ['id', '=', 'main', 'or', '@id = "main"'];
        yield 'data attribute' => ['data-role', '=', 'button', 'and', '@data-role = "button"'];
        yield 'href attribute' => ['href', '!=', '#', 'or', 'not(@href = "#")'];
    }

    public static function attributeSelectorProvider(): \Generator
    {
        yield 'normal attribute' => ['class', '=', 'test', '@class = "test"'];
        yield 'normalize-space function' => ['normalize-space(.)', '=', 'content', 'normalize-space(.) = "content"'];
        yield 'normalize-space text' => ['normalize-space(text())', 'contains', 'word', 'contains(normalize-space(text()), "word")'];
        yield 'at-prefixed attribute' => ['@existing', '=', 'value', '@existing = "value"'];
    }

    public static function operatorProvider(): \Generator
    {
        yield 'equals operator' => ['=', 'name', 'value', '@name = "value"'];
        yield 'not equals operator' => ['!=', 'type', 'hidden', 'not(@type = "hidden")'];
        yield 'contains operator' => ['contains', 'class', 'active', 'contains(@class, "active")'];
        yield 'not contains operator' => ['!contains', 'class', 'disabled', 'not(contains(@class, "disabled"))'];
        yield 'has operator' => ['has', 'required', null, '@required'];
        yield 'not has operator' => ['!has', 'disabled', null, 'not(@disabled)'];
    }
}

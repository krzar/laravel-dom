<?php

declare(strict_types=1);

namespace KrZar\LaravelDom\Tests\Unit\Query;

use KrZar\LaravelDom\Query\Connector;
use KrZar\LaravelDom\Query\Operator;
use KrZar\LaravelDom\Query\QueryItem;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class QueryItemTest extends TestCase
{
    #[DataProvider('basicQueryItemProvider')]
    public function test_basic_query_items(
        string $attribute,
        Operator $operator,
        ?string $value,
        Connector $connector,
        string $expected
    ): void {
        $queryItem = new QueryItem($attribute, $operator, $value, $connector);

        $this->assertEquals($expected, $queryItem->toQueryString());
        $this->assertEquals($connector, $queryItem->connector);
    }

    #[DataProvider('attributeSelectorProvider')]
    public function test_attribute_selectors(
        string $attribute,
        Operator $operator,
        ?string $value,
        string $expected
    ): void {
        $queryItem = new QueryItem($attribute, $operator, $value);

        $this->assertEquals($expected, $queryItem->toQueryString());
    }

    #[DataProvider('operatorProvider')]
    public function test_operators(Operator $operator, string $attribute, ?string $value, string $expected): void
    {
        $queryItem = new QueryItem($attribute, $operator, $value);

        $this->assertEquals($expected, $queryItem->toQueryString());
    }

    public function test_special_attributes(): void
    {
        $normalizeSpaceItem = new QueryItem('normalize-space(.)', Operator::EQUALS, 'text');
        $this->assertEquals('normalize-space(.) = "text"', $normalizeSpaceItem->toQueryString());

        $atAttributeItem = new QueryItem('@custom-attr', Operator::EQUALS, 'value');
        $this->assertEquals('@custom-attr = "value"', $atAttributeItem->toQueryString());
    }

    public static function basicQueryItemProvider(): \Generator
    {
        yield 'class equals with and' => ['class', Operator::EQUALS, 'container', Connector::AND, '@class = "container"'];
        yield 'id equals with or' => ['id', Operator::EQUALS, 'main', Connector::OR, '@id = "main"'];
        yield 'data attribute' => ['data-role', Operator::EQUALS, 'button', Connector::AND, '@data-role = "button"'];
        yield 'href attribute' => ['href', Operator::NOT_EQUALS, '#', Connector::OR, 'not(@href = "#")'];
    }

    public static function attributeSelectorProvider(): \Generator
    {
        yield 'normal attribute' => ['class', Operator::EQUALS, 'test', '@class = "test"'];
        yield 'normalize-space function' => ['normalize-space(.)', Operator::EQUALS, 'content', 'normalize-space(.) = "content"'];
        yield 'normalize-space text' => ['normalize-space(text())', Operator::CONTAINS, 'word', 'contains(normalize-space(text()), "word")'];
        yield 'at-prefixed attribute' => ['@existing', Operator::EQUALS, 'value', '@existing = "value"'];
    }

    public static function operatorProvider(): \Generator
    {
        yield 'equals operator' => [Operator::EQUALS, 'name', 'value', '@name = "value"'];
        yield 'not equals operator' => [Operator::NOT_EQUALS, 'type', 'hidden', 'not(@type = "hidden")'];
        yield 'contains operator' => [Operator::CONTAINS, 'class', 'active', 'contains(@class, "active")'];
        yield 'not contains operator' => [Operator::NOT_CONTAINS, 'class', 'disabled', 'not(contains(@class, "disabled"))'];
        yield 'has operator' => [Operator::HAS, 'required', null, '@required'];
        yield 'not has operator' => [Operator::NOT_HAS, 'disabled', null, 'not(@disabled)'];
    }
}

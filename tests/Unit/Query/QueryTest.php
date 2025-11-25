<?php

declare(strict_types=1);

namespace KrZar\LaravelDom\Tests\Unit\Query;

use KrZar\LaravelDom\Query\Query;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class QueryTest extends TestCase
{
    #[DataProvider('basicQueryProvider')]
    public function testBasicWhereQueries(string $tag, bool $deep, string $attribute, string $operator, ?string $value, string $expected): void
    {
        $query = new Query($tag, $deep);
        $query->where($attribute, $operator, $value);

        $this->assertEquals($expected, $query->toQueryString());
    }

    #[DataProvider('attributeExistenceProvider')]
    public function testAttributeExistenceQueries(string $tag, string $attribute, string $method, string $expected): void
    {
        $query = new Query($tag, false);
        $query->$method($attribute);

        $this->assertEquals($expected, $query->toQueryString());
    }

    #[DataProvider('orWhereProvider')]
    public function testOrWhereQueries(string $tag, array $conditions, string $expected): void
    {
        $query = new Query($tag, false);

        foreach ($conditions as $condition) {
            $method = $condition['method'];
            $args = $condition['args'];
            $query->$method(...$args);
        }

        $this->assertEquals($expected, $query->toQueryString());
    }

    #[DataProvider('textQueryProvider')]
    public function testTextQueries(string $tag, string $operator, ?string $value, bool $deep, string $expected): void
    {
        $query = new Query($tag, false);
        $query->whereText($operator, $value, $deep);

        $this->assertEquals($expected, $query->toQueryString());
    }

    #[DataProvider('complexQueryProvider')]
    public function testComplexQueries(string $tag, bool $deep, callable $builder, string $expected): void
    {
        $query = new Query($tag, $deep);
        $builder($query);

        $this->assertEquals($expected, $query->toQueryString());
    }

    public function testNestedQueries(): void
    {
        $query = new Query('div', false);
        $query->where(function (Query $subQuery): void {
            $subQuery->where('class', 'container')
                ->orWhere('id', 'main');
        });

        $expected = '/div[(@class = "container" or @id = "main")]';
        $this->assertEquals($expected, $query->toQueryString());
    }

    public static function basicQueryProvider(): \Generator
    {
        yield 'shallow div with class' => ['div', false, 'class', '=', 'container', '/div[@class = "container"]'];
        yield 'deep span with id' => ['span', true, 'id', '=', 'test', '//span[@id = "test"]'];
        yield 'attribute contains value' => ['p', false, 'class', 'contains', 'active', '/p[contains(@class, "active")]'];
        yield 'attribute not equal' => ['a', false, 'href', '!=', '#', '/a[not(@href = "#")]'];
        yield 'attribute not contains' => ['div', false, 'class', '!contains', 'hidden', '/div[not(contains(@class, "hidden"))]'];
    }

    public static function attributeExistenceProvider(): \Generator
    {
        yield 'has attribute' => ['input', 'required', 'whereHas', '/input[@required]'];
        yield 'not has attribute' => ['input', 'disabled', 'whereNotHas', '/input[not(@disabled)]'];
        yield 'or has attribute' => ['img', 'alt', 'orWhereHas', '/img[@alt]'];
        yield 'or not has attribute' => ['video', 'autoplay', 'orWhereNotHas', '/video[not(@autoplay)]'];
    }

    public static function orWhereProvider(): \Generator
    {
        yield 'multiple or conditions' => [
            'div',
            [
                ['method' => 'where', 'args' => ['class', 'primary']],
                ['method' => 'orWhere', 'args' => ['class', 'secondary']],
                ['method' => 'orWhere', 'args' => ['id', 'main']],
            ],
            '/div[@class = "primary" or @class = "secondary" or @id = "main"]',
        ];
        yield 'mixed and/or conditions' => [
            'span',
            [
                ['method' => 'where', 'args' => ['class', 'highlight']],
                ['method' => 'where', 'args' => ['data-type', 'important']],
                ['method' => 'orWhere', 'args' => ['class', 'error']],
            ],
            '/span[@class = "highlight" and @data-type = "important" or @class = "error"]',
        ];
    }

    public static function textQueryProvider(): \Generator
    {
        yield 'text equals shallow' => ['p', '=', 'Hello', false, '/p[normalize-space(text()) = "Hello"]'];
        yield 'text equals deep' => ['div', '=', 'Content', true, '/div[normalize-space(.) = "Content"]'];
        yield 'text contains shallow' => ['span', 'contains', 'test', false, '/span[contains(normalize-space(text()), "test")]'];
        yield 'text contains deep' => ['article', 'contains', 'news', true, '/article[contains(normalize-space(.), "news")]'];
        yield 'text not equal' => ['h1', '!=', 'Title', false, '/h1[not(normalize-space(text()) = "Title")]'];
    }

    public static function complexQueryProvider(): \Generator
    {
        yield 'multiple conditions' => [
            'div',
            false,
            function (Query $q): void {
                $q->where('class', 'container')
                    ->where('id', 'main')
                    ->whereHas('data-role');
            },
            '/div[@class = "container" and @id = "main" and @data-role]',
        ];
        yield 'text and attribute conditions' => [
            'button',
            false,
            function (Query $q): void {
                $q->where('type', 'submit')
                    ->whereText('=', 'Submit')
                    ->whereNotHas('disabled');
            },
            '/button[@type = "submit" and normalize-space(text()) = "Submit" and not(@disabled)]',
        ];
        yield 'deep search with multiple conditions' => [
            'a',
            true,
            function (Query $q): void {
                $q->whereHas('href')
                    ->where('class', 'contains', 'link')
                    ->orWhereText('contains', 'Click');
            },
            '//a[@href and contains(@class, "link") or contains(normalize-space(text()), "Click")]',
        ];
    }
}

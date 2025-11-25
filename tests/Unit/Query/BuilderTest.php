<?php

declare(strict_types=1);

namespace KrZar\LaravelDom\Tests\Unit\Query;

use KrZar\LaravelDom\Document;
use KrZar\LaravelDom\DocumentNode;
use KrZar\LaravelDom\Query\Query;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class BuilderTest extends TestCase
{
    #[DataProvider('htmlSampleProvider')]
    public function testQueryAndGet(string $html, string $tag, callable $queryBuilder, int $expectedCount): void
    {
        $document = Document::loadHtml($html);
        $result = $document->query($tag, $queryBuilder, true)->get();

        $this->assertCount($expectedCount, $result);
    }

    #[DataProvider('firstElementProvider')]
    public function testQueryAndFirst(string $html, string $tag, callable $queryBuilder, ?string $expectedText): void
    {
        $document = Document::loadHtml($html);
        $result = $document->query($tag, $queryBuilder, true)->first();

        if ($expectedText === null) {
            $this->assertNull($result);
        } else {
            $this->assertNotNull($result);
            $this->assertInstanceOf(DocumentNode::class, $result);
            $this->assertEquals($expectedText, $result->text());
        }
    }

    public function testMultipleQueries(): void
    {
        $html = '<html><body><div class="container"><p>First</p><p class="highlight">Second</p></div></body></html>';
        $document = Document::loadHtml($html);

        $result = $document
            ->query('div', function (Query $q): void {
                $q->where('class', 'container');
            }, true)
            ->query('p', function (Query $q): void {
                $q->where('class', 'highlight');
            }, true)
            ->get();

        $this->assertCount(1, $result);
        $this->assertEquals('Second', $result->first()->text());
    }

    public function testEmptyResult(): void
    {
        $html = '<html><body><div>Content</div></body></html>';
        $document = Document::loadHtml($html);

        $result = $document->query('span', function (Query $q): void {
            $q->where('class', 'nonexistent');
        }, true)->get();

        $this->assertCount(0, $result);

        $first = $document->query('span', function (Query $q): void {
            $q->where('class', 'nonexistent');
        }, true)->first();

        $this->assertNull($first);
    }

    public static function htmlSampleProvider(): \Generator
    {
        yield 'multiple divs with class' => [
            '<html><body><div class="item">1</div><div class="item">2</div><div>3</div></body></html>',
            'div',
            function (Query $q): void {
                $q->where('class', 'item');
            },
            2,
        ];
        yield 'deep search for spans' => [
            '<html><body><div><span>1</span></div><div><p><span>2</span></p></div></body></html>',
            'span',
            function (Query $q): void {},
            2,
        ];
        yield 'complex attribute conditions' => [
            '<html><body><input type="text" required><input type="email"><input type="text"></body></html>',
            'input',
            function (Query $q): void {
                $q->where('type', 'text')->whereHas('required');
            },
            1,
        ];
    }

    public static function firstElementProvider(): \Generator
    {
        yield 'first paragraph' => [
            '<html><body><p>First</p><p>Second</p></body></html>',
            'p',
            function (Query $q): void {},
            'First',
        ];
        yield 'no matching elements' => [
            '<html><body><div>Content</div></body></html>',
            'span',
            function (Query $q): void {},
            null,
        ];
        yield 'first with specific class' => [
            '<html><body><div class="other">Other</div><div class="target">Target</div></body></html>',
            'div',
            function (Query $q): void {
                $q->where('class', 'target');
            },
            'Target',
        ];
    }
}

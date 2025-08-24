<?php

declare(strict_types=1);

namespace KrZar\LaravelDom\Tests\Unit;

use KrZar\LaravelDom\Document;
use KrZar\LaravelDom\DocumentElement;
use KrZar\LaravelDom\DocumentNode;
use KrZar\LaravelDom\Query\Query;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class DocumentNodeTest extends TestCase
{
    #[DataProvider('textContentProvider')]
    public function test_text(string $html, string $selector, string $expected): void
    {
        $document = Document::loadHtml($html);
        $node = $document->query($selector, function (Query $q): void {}, true)->first();

        $this->assertNotNull($node);
        $this->assertEquals($expected, $node->text());
    }

    #[DataProvider('attributeProvider')]
    public function test_attribute(string $html, string $selector, string $attributeName, mixed $expected, mixed $default = null): void
    {
        $document = Document::loadHtml($html);
        $node = $document->query($selector, function (Query $q): void {}, true)->first();

        $this->assertNotNull($node);
        $this->assertEquals($expected, $node->attribute($attributeName, $default));
    }

    #[DataProvider('htmlContentProvider')]
    public function test_html(string $html, string $selector, string $expectedContains): void
    {
        $document = Document::loadHtml($html);
        $node = $document->query($selector, function (Query $q): void {}, true)->first();

        $this->assertNotNull($node);
        $htmlContent = $node->html();
        $this->assertNotNull($htmlContent);
        $this->assertStringContainsString($expectedContains, $htmlContent);
    }

    #[DataProvider('childrenProvider')]
    public function test_children(string $html, string $selector, int $expectedCount, array $expectedTypes): void
    {
        $document = Document::loadHtml($html);
        $node = $document->query($selector, function (Query $q): void {}, true)->first();

        $this->assertNotNull($node);
        $children = $node->children();

        $this->assertCount($expectedCount, $children);

        foreach ($expectedTypes as $index => $expectedType) {
            $this->assertInstanceOf($expectedType, $children->get($index));
        }
    }

    #[DataProvider('navigationProvider')]
    public function test_navigation(string $html, string $selector, string $method, ?string $expectedText): void
    {
        $document = Document::loadHtml($html);
        $node = $document->query($selector, function (Query $q): void {}, true)->first();

        $this->assertNotNull($node);
        $result = $node->$method();

        if ($expectedText === null) {
            $this->assertNull($result);
        } else {
            $this->assertNotNull($result);
            $this->assertEquals($expectedText, $result->text());
        }
    }

    public function test_query(): void
    {
        $html = '<html><body><div><p class="test">Hello</p><p>World</p></div></body></html>';
        $document = Document::loadHtml($html);
        $div = $document->query('div', function (Query $q): void {}, true)->first();

        $this->assertNotNull($div);

        $result = $div->query('p', function (Query $q): void {
            $q->where('class', 'test');
        })->get();

        $this->assertCount(1, $result);
        $this->assertEquals('Hello', $result->first()->text());
    }

    public static function textContentProvider(): \Generator
    {
        yield 'simple paragraph' => [
            '<html><body><p>Hello World</p></body></html>',
            'p',
            'Hello World',
        ];
        yield 'nested content' => [
            '<html><body><div>  <span>Nested</span>  Text  </div></body></html>',
            'div',
            'Nested  Text',
        ];
        yield 'empty element' => [
            '<html><body><div></div></body></html>',
            'div',
            '',
        ];
    }

    public static function attributeProvider(): \Generator
    {
        yield 'existing attribute' => [
            '<html><body><div class="container">Content</div></body></html>',
            'div',
            'class',
            'container',
        ];
        yield 'non-existing attribute with default' => [
            '<html><body><div>Content</div></body></html>',
            'div',
            'id',
            'default-value',
            'default-value',
        ];
        yield 'non-existing attribute without default' => [
            '<html><body><div>Content</div></body></html>',
            'div',
            'id',
            null,
        ];
        yield 'data attribute' => [
            '<html><body><input data-role="button" /></body></html>',
            'input',
            'data-role',
            'button',
        ];
    }

    public static function htmlContentProvider(): \Generator
    {
        yield 'div with content' => [
            '<html><body><div class="test">Hello <span>World</span></div></body></html>',
            'div',
            '<div class="test">Hello <span>World</span></div>',
        ];
        yield 'paragraph' => [
            '<html><body><p>Simple text</p></body></html>',
            'p',
            '<p>Simple text</p>',
        ];
        yield 'input element' => [
            '<html><body><input type="text" value="test" /></body></html>',
            'input',
            '<input type="text" value="test"',
        ];
    }

    public static function childrenProvider(): \Generator
    {
        yield 'mixed children' => [
            '<html><body><div><p>Para</p><span>Span</span></div></body></html>',
            'div',
            2,
            [DocumentElement::class, DocumentElement::class],
        ];
        yield 'no children' => [
            '<html><body><p>Text only</p></body></html>',
            'p',
            1,
            [DocumentNode::class],
        ];
        yield 'nested structure' => [
            '<html><body><article><header>Header</header><section>Content</section></article></body></html>',
            'article',
            2,
            [DocumentElement::class, DocumentElement::class],
        ];
    }

    public static function navigationProvider(): \Generator
    {
        yield 'parent exists' => [
            '<html><body><div><p>Child</p></div></body></html>',
            'p',
            'parent',
            'Child',
        ];
        yield 'next sibling exists' => [
            '<html><body><div><p>First</p><span>Second</span></div></body></html>',
            'p',
            'nextSibling',
            'Second',
        ];
        yield 'previous sibling exists' => [
            '<html><body><div><p>First</p><span>Second</span></div></body></html>',
            'span',
            'previousSibling',
            'First',
        ];
        yield 'no next sibling' => [
            '<html><body><div><p>Only child</p></div></body></html>',
            'p',
            'nextSibling',
            null,
        ];
    }
}

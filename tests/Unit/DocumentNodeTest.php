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

    public function test_query_deep_finds_deeply_nested_elements(): void
    {
        $html = '<html><body><div><article><section><p class="deep">Deeply nested</p></section></article><p class="shallow">Shallow</p></div></body></html>';
        $document = Document::loadHtml($html);
        $div = $document->query('div', function (Query $q): void {}, true)->first();

        $this->assertNotNull($div);

        $result = $div->queryDeep('p', function (Query $q): void {
            $q->where('class', 'deep');
        })->get();

        $this->assertCount(1, $result);
        $this->assertEquals('Deeply nested', $result->first()->text());
    }

    public function test_query_deep_finds_all_descendants(): void
    {
        $html = '<html><body><div><span>Level 1</span><section><span>Level 2</span><article><span>Level 3</span></article></section></div></body></html>';
        $document = Document::loadHtml($html);
        $div = $document->query('div', function (Query $q): void {}, true)->first();

        $this->assertNotNull($div);

        $result = $div->queryDeep('span', function (Query $q): void {})->get();

        $this->assertCount(3, $result);
        $this->assertEquals('Level 1', $result->get(0)->text());
        $this->assertEquals('Level 2', $result->get(1)->text());
        $this->assertEquals('Level 3', $result->get(2)->text());
    }

    public function test_query_deep_with_complex_conditions(): void
    {
        $html = '<html><body><div><p data-type="info">Info 1</p><section><p data-type="warning">Warning</p><article><p data-type="info">Info 2</p></article></section></div></body></html>';
        $document = Document::loadHtml($html);
        $div = $document->query('div', function (Query $q): void {}, true)->first();

        $this->assertNotNull($div);

        $result = $div->queryDeep('p', function (Query $q): void {
            $q->where('data-type', 'info');
        })->get();

        $this->assertCount(2, $result);
        $this->assertEquals('Info 1', $result->get(0)->text());
        $this->assertEquals('Info 2', $result->get(1)->text());
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
            '<div class="test">Hello <span>World</span>',
        ];
        yield 'paragraph' => [
            '<html><body><p>Simple text</p></body></html>',
            'p',
            '<p>Simple text',
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

    public function test_set_text(): void
    {
        $document = Document::loadHtml('<html><body><div>Original text</div></body></html>');
        $node = $document->query('div', fn ($q) => null, true)->first();

        $this->assertEquals('Original text', $node->text());
        $node->setText('New text');
        $this->assertEquals('New text', $node->text());
    }

    public function test_set_text_replaces_all_content(): void
    {
        $document = Document::loadHtml('<html><body><div>Text with <span>nested</span> content</div></body></html>');
        $node = $document->query('div', fn ($q) => null, true)->first();

        $node->setText('Simple text');
        $this->assertEquals('Simple text', $node->text());

        $result = $node->query('span', fn ($q) => null, true)->first();
        $this->assertNull($result);
    }

    public function test_document_returns_parent_document(): void
    {
        $document = Document::loadHtml('<html><body><div>Test</div></body></html>');
        $node = $document->query('div', fn ($q) => null, true)->first();

        $parentDocument = $node->document();
        $this->assertSame($document, $parentDocument);
    }
}

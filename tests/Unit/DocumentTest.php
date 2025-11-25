<?php

declare(strict_types=1);

namespace KrZar\LaravelDom\Tests\Unit;

use KrZar\LaravelDom\Document;
use KrZar\LaravelDom\DocumentElement;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class DocumentTest extends TestCase
{
    #[DataProvider('htmlDocumentProvider')]
    public function test_load_html(string $html, string $expectedTag, string $expectedText): void
    {
        $document = Document::loadHtml($html);

        $firstElement = $document->query($expectedTag, fn ($q) => null, true)->first();
        $this->assertInstanceOf(DocumentElement::class, $firstElement);
        $this->assertEquals($expectedText, $firstElement->text());
    }

    #[DataProvider('xmlDocumentProvider')]
    public function test_load_xml(string $xml, string $expectedTag, string $expectedText): void
    {
        $document = Document::loadXml($xml);

        $firstElement = $document->query($expectedTag, fn ($q) => null, true)->first();
        $this->assertInstanceOf(DocumentElement::class, $firstElement);
        $this->assertEquals($expectedText, $firstElement->text());
    }

    public static function htmlDocumentProvider(): \Generator
    {
        yield 'simple paragraph' => [
            '<html><body><p>Hello World</p></body></html>',
            'p',
            'Hello World',
        ];
        yield 'div with content' => [
            '<html><body><div>Test Content</div></body></html>',
            'div',
            'Test Content',
        ];
        yield 'header element' => [
            '<html><body><h1>Main Title</h1></body></html>',
            'h1',
            'Main Title',
        ];
        yield 'span with text' => [
            '<html><body><span>Inline text</span></body></html>',
            'span',
            'Inline text',
        ];
    }

    public static function xmlDocumentProvider(): \Generator
    {
        yield 'simple root element' => [
            '<?xml version="1.0"?><root>Root Content</root>',
            'root',
            'Root Content',
        ];
        yield 'nested element' => [
            '<?xml version="1.0"?><root><item>Item Content</item></root>',
            'item',
            'Item Content',
        ];
        yield 'element with attributes' => [
            '<?xml version="1.0"?><root><product name="test">Product Info</product></root>',
            'product',
            'Product Info',
        ];
    }

    public function test_create_empty_document(): void
    {
        $document = Document::create('1.0', 'UTF-8');
        $nativeDoc = $document->toNative();

        $this->assertEquals('1.0', $nativeDoc->version);
        $this->assertEquals('UTF-8', $nativeDoc->encoding);
    }

    public function test_append_element_to_document(): void
    {
        $document = Document::create();
        $rootElement = $document->toNative()->createElement('root');
        $documentElement = new DocumentElement($document, $rootElement);

        $document->append($documentElement);

        $result = $document->query('root', fn ($q) => null, true)->first();

        $this->assertInstanceOf(DocumentElement::class, $result);
        $this->assertEquals('root', $result->tagName());
    }

    public function test_prepend_element_to_document(): void
    {
        $document = Document::loadHtml('<html><body><div>Existing</div></body></html>');
        $newElement = $document->toNative()->createElement('header');
        $documentElement = new DocumentElement($document, $newElement);

        $document->prepend($documentElement);

        $children = $document->children();
        $this->assertGreaterThan(0, $children->count());
    }
}

<?php

declare(strict_types=1);

namespace KrZar\LaravelDom\Tests\Unit;

use KrZar\LaravelDom\Document;
use KrZar\LaravelDom\DocumentElement;
use KrZar\LaravelDom\Query\Query;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class DocumentElementTest extends TestCase
{
    #[DataProvider('elementBasicsProvider')]
    public function test_element_basics(string $html, string $selector, string $expectedTag, ?string $expectedId, ?string $expectedClass): void
    {
        $document = Document::loadHtml($html);
        $element = $document->query($selector, function (Query $q): void {}, true)->first();

        $this->assertInstanceOf(DocumentElement::class, $element);
        $this->assertEquals($expectedTag, $element->tagName());
        $this->assertEquals($expectedId, $element->id());
        $this->assertEquals($expectedClass, $element->className());
    }

    #[DataProvider('classesProvider')]
    public function test_classes(string $html, string $selector, array $expectedClasses): void
    {
        $document = Document::loadHtml($html);
        $element = $document->query($selector, function (Query $q): void {}, true)->first();

        $this->assertInstanceOf(DocumentElement::class, $element);
        $this->assertEquals($expectedClasses, $element->classes());
    }

    #[DataProvider('attributesProvider')]
    public function test_attributes(string $html, string $selector, array $expectedAttributes): void
    {
        $document = Document::loadHtml($html);
        $element = $document->query($selector, function (Query $q): void {}, true)->first();

        $this->assertInstanceOf(DocumentElement::class, $element);
        $attributes = $element->attributes();

        foreach ($expectedAttributes as $name => $value) {
            $this->assertEquals($value, $attributes->get($name));
        }
    }

    #[DataProvider('parentProvider')]
    public function test_parent(string $html, string $selector, ?string $expectedParentTag): void
    {
        $document = Document::loadHtml($html);
        $element = $document->query($selector, function (Query $q): void {}, true)->first();

        $parent = $element->parent();

        if ($expectedParentTag === null) {
            $this->assertNull($parent);
        } else {
            $this->assertInstanceOf(DocumentElement::class, $parent);
            $this->assertEquals($expectedParentTag, $parent->tagName());
        }
    }

    #[DataProvider('complexElementProvider')]
    public function test_complex_elements(string $html, string $selector, array $expectations): void
    {
        $document = Document::loadHtml($html);
        $element = $document->query($selector, function (Query $q): void {
            $q->where('class', 'contains', 'test');
        }, true)->first();

        foreach ($expectations as $method => $expected) {
            $this->assertEquals($expected, $element->$method());
        }
    }

    public static function elementBasicsProvider(): \Generator
    {
        yield 'div with id and class' => [
            '<html><body><div id="main" class="container">Content</div></body></html>',
            'div',
            'div',
            'main',
            'container',
        ];
        yield 'span without id' => [
            '<html><body><span class="highlight">Text</span></body></html>',
            'span',
            'span',
            null,
            'highlight',
        ];
        yield 'paragraph without class' => [
            '<html><body><p id="intro">Paragraph</p></body></html>',
            'p',
            'p',
            'intro',
            null,
        ];
        yield 'input element' => [
            '<html><body><input type="text" id="field" class="form-control" /></body></html>',
            'input',
            'input',
            'field',
            'form-control',
        ];
    }

    public static function classesProvider(): \Generator
    {
        yield 'multiple classes' => [
            '<html><body><div class="btn btn-primary active">Button</div></body></html>',
            'div',
            ['btn', 'btn-primary', 'active'],
        ];
        yield 'single class' => [
            '<html><body><span class="highlight">Text</span></body></html>',
            'span',
            ['highlight'],
        ];
        yield 'no class' => [
            '<html><body><p>No class</p></body></html>',
            'p',
            [],
        ];
        yield 'empty class' => [
            '<html><body><div class="">Empty</div></body></html>',
            'div',
            [''],
        ];
    }

    public static function attributesProvider(): \Generator
    {
        yield 'form input attributes' => [
            '<html><body><input type="email" name="email" required placeholder="Enter email" /></body></html>',
            'input',
            [
                'type' => 'email',
                'name' => 'email',
                'required' => '',
                'placeholder' => 'Enter email',
            ],
        ];
        yield 'link attributes' => [
            '<html><body><a href="https://example.com" target="_blank" rel="noopener">Link</a></body></html>',
            'a',
            [
                'href' => 'https://example.com',
                'target' => '_blank',
                'rel' => 'noopener',
            ],
        ];
        yield 'data attributes' => [
            '<html><body><div data-id="123" data-role="button" data-toggle="modal">Element</div></body></html>',
            'div',
            [
                'data-id' => '123',
                'data-role' => 'button',
                'data-toggle' => 'modal',
            ],
        ];
    }

    public static function parentProvider(): \Generator
    {
        yield 'div parent' => [
            '<html><body><div><p>Child</p></div></body></html>',
            'p',
            'div',
        ];
        yield 'section parent' => [
            '<html><body><section><article><h1>Title</h1></article></section></body></html>',
            'h1',
            'article',
        ];
        yield 'body parent' => [
            '<html><body><div>Direct child</div></body></html>',
            'div',
            'body',
        ];
    }

    public static function complexElementProvider(): \Generator
    {
        yield 'form element' => [
            '<html><body><form class="test-form" method="post" action="/submit"><input type="hidden" name="token" value="abc123" /></form></body></html>',
            'form',
            [
                'tagName' => 'form',
                'className' => 'test-form',
            ],
        ];
        yield 'div with nested content' => [
            '<html><body><div class="post test-article" id="post-1"><h2>Title</h2><p>Content</p></div></body></html>',
            'div',
            [
                'tagName' => 'div',
                'id' => 'post-1',
                'className' => 'post test-article',
            ],
        ];
    }
}

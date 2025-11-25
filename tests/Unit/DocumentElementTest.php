<?php

declare(strict_types=1);

namespace KrZar\LaravelDom\Tests\Unit;

use Illuminate\Support\Collection;
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
    public function test_classes(string $html, string $selector, Collection $expectedClasses): void
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
            collect(['btn', 'btn-primary', 'active']),
        ];
        yield 'single class' => [
            '<html><body><span class="highlight">Text</span></body></html>',
            'span',
            collect(['highlight']),
        ];
        yield 'no class' => [
            '<html><body><p>No class</p></body></html>',
            'p',
            collect(),
        ];
        yield 'empty class' => [
            '<html><body><div class="">Empty</div></body></html>',
            'div',
            collect(),
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

    public function test_set_id(): void
    {
        $document = Document::loadHtml('<html><body><div>Test</div></body></html>');
        $element = $document->query('div', fn ($q) => null, true)->first();

        $this->assertNull($element->id());
        $element->setId('new-id');
        $this->assertEquals('new-id', $element->id());
    }

    public function test_has_class(): void
    {
        $document = Document::loadHtml('<html><body><div class="btn btn-primary active">Button</div></body></html>');
        $element = $document->query('div', fn ($q) => null, true)->first();

        $this->assertTrue($element->hasClass('btn'));
        $this->assertTrue($element->hasClass('btn-primary'));
        $this->assertTrue($element->hasClass('active'));
        $this->assertFalse($element->hasClass('hidden'));
    }

    public function test_add_class(): void
    {
        $document = Document::loadHtml('<html><body><div class="btn">Button</div></body></html>');
        $element = $document->query('div', fn ($q) => null, true)->first();

        $this->assertFalse($element->hasClass('active'));
        $element->addClass('active');
        $this->assertTrue($element->hasClass('active'));
        $this->assertEquals('btn active', $element->className());
    }

    public function test_add_class_to_element_without_class(): void
    {
        $document = Document::loadHtml('<html><body><div>Button</div></body></html>');
        $element = $document->query('div', fn ($q) => null, true)->first();

        $element->addClass('btn');
        $this->assertTrue($element->hasClass('btn'));
        $this->assertEquals('btn', $element->className());
    }

    public function test_remove_class(): void
    {
        $document = Document::loadHtml('<html><body><div class="btn btn-primary active">Button</div></body></html>');
        $element = $document->query('div', fn ($q) => null, true)->first();

        $this->assertTrue($element->hasClass('active'));
        $element->removeClass('active');
        $this->assertFalse($element->hasClass('active'));
        $this->assertEquals('btn btn-primary', $element->className());
    }

    public function test_remove_class_removes_only_specified_class(): void
    {
        $document = Document::loadHtml('<html><body><div class="btn btn-primary btn-large">Button</div></body></html>');
        $element = $document->query('div', fn ($q) => null, true)->first();

        $element->removeClass('btn-primary');
        $this->assertTrue($element->hasClass('btn'));
        $this->assertTrue($element->hasClass('btn-large'));
        $this->assertFalse($element->hasClass('btn-primary'));
    }

    public function test_has_attribute(): void
    {
        $document = Document::loadHtml('<html><body><input type="text" name="email" required /></body></html>');
        $element = $document->query('input', fn ($q) => null, true)->first();

        $this->assertTrue($element->hasAttribute('type'));
        $this->assertTrue($element->hasAttribute('name'));
        $this->assertTrue($element->hasAttribute('required'));
        $this->assertFalse($element->hasAttribute('disabled'));
        $this->assertFalse($element->hasAttribute('placeholder'));
    }

    public function test_set_attribute(): void
    {
        $document = Document::loadHtml('<html><body><div>Test</div></body></html>');
        $element = $document->query('div', fn ($q) => null, true)->first();

        $this->assertFalse($element->hasAttribute('data-role'));
        $element->setAttribute('data-role', 'button');
        $this->assertTrue($element->hasAttribute('data-role'));
        $this->assertEquals('button', $element->attribute('data-role'));
    }

    public function test_set_attribute_overrides_existing(): void
    {
        $document = Document::loadHtml('<html><body><input type="text" /></body></html>');
        $element = $document->query('input', fn ($q) => null, true)->first();

        $this->assertEquals('text', $element->attribute('type'));
        $element->setAttribute('type', 'email');
        $this->assertEquals('email', $element->attribute('type'));
    }

    public function test_remove_attribute(): void
    {
        $document = Document::loadHtml('<html><body><div class="container" id="main">Test</div></body></html>');
        $element = $document->query('div', fn ($q) => null, true)->first();

        $this->assertTrue($element->hasAttribute('class'));
        $this->assertTrue($element->hasAttribute('id'));

        $element->removeAttribute('class');
        $this->assertFalse($element->hasAttribute('class'));
        $this->assertTrue($element->hasAttribute('id'));

        $element->removeAttribute('id');
        $this->assertFalse($element->hasAttribute('id'));
    }

    public function test_create_standalone_element(): void
    {
        $element = DocumentElement::create('div');

        $this->assertEquals('div', $element->tagName());
    }

    public function test_append_child_element(): void
    {
        $document = Document::loadHtml('<html><body><div id="parent"></div></body></html>');
        $parent = $document->query('div', fn ($q) => null, true)->first();

        $child = $document->toNative()->createElement('span');
        $child->textContent = 'Child text';
        $childElement = new DocumentElement($document, $child);

        $parent->append($childElement);

        $result = $parent->query('span', fn ($q) => null, true)->first();
        $this->assertEquals('Child text', $result->text());
    }

    public function test_prepend_child_element(): void
    {
        $document = Document::loadHtml('<html><body><div id="parent"><p>Existing</p></div></body></html>');
        $parent = $document->query('div', fn ($q) => null, true)->first();

        $child = $document->toNative()->createElement('span');
        $child->textContent = 'First child';
        $childElement = new DocumentElement($document, $child);

        $parent->prepend($childElement);

        $children = $parent->children()->filter(fn ($child) => $child instanceof DocumentElement);
        $firstChild = $children->first();
        $this->assertEquals('span', $firstChild->tagName());
        $this->assertEquals('First child', $firstChild->text());
    }

    public function test_remove_element(): void
    {
        $document = Document::loadHtml('<html><body><div><span id="to-remove">Remove me</span><p>Keep me</p></div></body></html>');
        $element = $document->query('span', fn ($q) => null, true)->first();
        $parent = $element->parent();

        $this->assertNotNull($element);
        $element->remove();

        $result = $parent->query('span', fn ($q) => null, true)->first();
        $this->assertNull($result);

        $paragraph = $parent->query('p', fn ($q) => null, true)->first();
        $this->assertNotNull($paragraph);
        $this->assertEquals('Keep me', $paragraph->text());
    }
}

<?php

declare(strict_types=1);

namespace KrZar\LaravelDom\Tests\Integration;

use KrZar\LaravelDom\Document;
use KrZar\LaravelDom\DocumentElement;
use KrZar\LaravelDom\Query\Query;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class QueryScenariosTest extends TestCase
{
    #[DataProvider('realWorldHtmlProvider')]
    public function testRealWorldQueries(string $html, callable $queryBuilder, array $expectedResults): void
    {
        $document = Document::loadHtml($html);
        $results = $queryBuilder($document);

        $this->assertCount(count($expectedResults), $results);

        foreach ($expectedResults as $index => $expected) {
            $element = $results->get($index);
            $this->assertInstanceOf(DocumentElement::class, $element);

            if (isset($expected['text'])) {
                $this->assertEquals($expected['text'], $element->text());
            }
            if (isset($expected['tag'])) {
                $this->assertEquals($expected['tag'], $element->tagName());
            }
            if (isset($expected['id'])) {
                $this->assertEquals($expected['id'], $element->id());
            }
            if (isset($expected['class'])) {
                $this->assertEquals($expected['class'], $element->className());
            }
        }
    }

    #[DataProvider('formQueryProvider')]
    public function testFormQueries(string $html, callable $queryBuilder, array $expectedInputs): void
    {
        $document = Document::loadHtml($html);
        $inputs = $queryBuilder($document);

        $this->assertCount(count($expectedInputs), $inputs);

        foreach ($expectedInputs as $index => $expected) {
            $input = $inputs->get($index);
            $this->assertEquals($expected['type'], $input->attribute('type'));

            if (isset($expected['name'])) {
                $this->assertEquals($expected['name'], $input->attribute('name'));
            }
            if (isset($expected['required'])) {
                $this->assertEquals($expected['required'], $input->attribute('required') !== null);
            }
        }
    }

    #[DataProvider('navigationQueryProvider')]
    public function testNavigationQueries(string $html, callable $queryBuilder, array $expectedLinks): void
    {
        $document = Document::loadHtml($html);
        $links = $queryBuilder($document);

        $this->assertCount(count($expectedLinks), $links);

        foreach ($expectedLinks as $index => $expected) {
            $link = $links->get($index);
            $this->assertEquals($expected['text'], $link->text());
            $this->assertEquals($expected['href'], $link->attribute('href'));
        }
    }

    #[DataProvider('deepSearchProvider')]
    public function testDeepSearchQueries(string $html, callable $queryBuilder, int $expectedCount, array $expectedTexts): void
    {
        $document = Document::loadHtml($html);
        $results = $queryBuilder($document);

        $this->assertCount($expectedCount, $results);

        foreach ($expectedTexts as $index => $expectedText) {
            $this->assertEquals($expectedText, $results->get($index)->text());
        }
    }

    #[DataProvider('conditionalQueryProvider')]
    public function testConditionalQueries(string $html, callable $queryBuilder, array $expectedElements): void
    {
        $document = Document::loadHtml($html);
        $results = $queryBuilder($document);

        foreach ($expectedElements as $index => $expected) {
            $element = $results->get($index);

            foreach ($expected as $property => $value) {
                if ($property === 'hasAttribute') {
                    $this->assertNotNull($element->attribute($value));
                } elseif ($property === 'notHasAttribute') {
                    $this->assertNull($element->attribute($value));
                } else {
                    $this->assertEquals($value, $element->$property());
                }
            }
        }
    }

    public static function realWorldHtmlProvider(): \Generator
    {
        yield 'blog post structure' => [
            '<html><body>
                <article class="post">
                    <header><h1>Blog Title</h1></header>
                    <section class="content"><p>First paragraph</p><p>Second paragraph</p></section>
                    <footer class="meta">By Author</footer>
                </article>
            </body></html>',
            function (Document $doc) {
                return $doc->query('section', function (Query $q): void {
                    $q->where('class', 'content');
                }, true)->query('p', function (Query $q): void {}, true)->get();
            },
            [
                ['text' => 'First paragraph', 'tag' => 'p'],
                ['text' => 'Second paragraph', 'tag' => 'p'],
            ],
        ];
        yield 'navigation menu' => [
            '<html><body>
                <nav class="main-menu">
                    <ul>
                        <li><a href="/" class="active">Home</a></li>
                        <li><a href="/about">About</a></li>
                        <li><a href="/contact" class="disabled">Contact</a></li>
                    </ul>
                </nav>
            </body></html>',
            function (Document $doc) {
                return $doc->query('a', function (Query $q): void {
                    $q->whereNotHas('class')
                        ->orWhere('class', '!=', 'disabled');
                }, true)->get();
            },
            [
                ['text' => 'Home', 'class' => 'active'],
                ['text' => 'About', 'class' => null],
            ],
        ];
        yield 'html5 semantic structure' => [
            '<html><body>
                <header><nav><a href="/">Home</a></nav></header>
                <main>
                    <section><article><h1>Title</h1> <p>Content</p></article></section>
                    <aside><article><h2>Sidebar</h2></article></aside>
                </main>
                <footer><p>Footer text</p></footer>
            </body></html>',
            function (Document $doc) {
                return $doc->query('article', function (Query $q): void {}, true)->get();
            },
            [
                ['text' => 'Title Content', 'tag' => 'article'],
                ['text' => 'Sidebar', 'tag' => 'article'],
            ],
        ];
    }

    public static function formQueryProvider(): \Generator
    {
        yield 'contact form' => [
            '<html><body>
                <form>
                    <input type="text" name="name" required />
                    <input type="email" name="email" required />
                    <input type="tel" name="phone" />
                    <input type="hidden" name="token" value="abc123" />
                    <textarea name="message" required></textarea>
                    <button type="submit">Send</button>
                </form>
            </body></html>',
            function (Document $doc) {
                return $doc->query('input', function (Query $q): void {
                    $q->where('type', '!=', 'hidden')
                        ->whereHas('required');
                }, true)->get();
            },
            [
                ['type' => 'text', 'name' => 'name', 'required' => true],
                ['type' => 'email', 'name' => 'email', 'required' => true],
            ],
        ];
    }

    public static function navigationQueryProvider(): \Generator
    {
        yield 'external links' => [
            '<html><body>
                <div class="content">
                    <p>Visit <a href="https://example.com">Example</a> for more info.</p>
                    <p>Or check our <a href="/internal">internal page</a>.</p>
                    <p>Email us at <a href="mailto:test@example.com">test@example.com</a>.</p>
                </div>
            </body></html>',
            function (Document $doc) {
                return $doc->query('a', function (Query $q): void {
                    $q->where('href', 'contains', 'http');
                }, true)->get();
            },
            [
                ['text' => 'Example', 'href' => 'https://example.com'],
            ],
        ];
    }

    public static function deepSearchProvider(): \Generator
    {
        yield 'nested spans' => [
            '<html><body>
                <div class="container">
                    <section>
                        <article>
                            <span class="highlight">Deep text 1</span>
                            <p>Some paragraph with <span class="highlight">Deep text 2</span></p>
                        </article>
                    </section>
                    <aside>
                        <span class="highlight">Deep text 3</span>
                    </aside>
                </div>
            </body></html>',
            function (Document $doc) {
                return $doc->query('span', function (Query $q): void {
                    $q->where('class', 'highlight');
                }, true)->get();
            },
            3,
            ['Deep text 1', 'Deep text 2', 'Deep text 3'],
        ];
    }

    public static function conditionalQueryProvider(): \Generator
    {
        yield 'conditional attributes' => [
            '<html><body>
                <div class="card" data-id="1">Card 1</div>
                <div class="card featured" data-id="2" data-featured="true">Card 2</div>
                <div class="card" data-id="3">Card 3</div>
            </body></html>',
            function (Document $doc) {
                return $doc->query('div', function (Query $q): void {
                    $q->where('class', 'contains', 'featured')
                        ->whereHas('data-featured');
                }, true)->get();
            },
            [
                [
                    'text' => 'Card 2',
                    'className' => 'card featured',
                    'hasAttribute' => 'data-featured',
                ],
            ],
        ];
    }

    #[DataProvider('searchModeProvider')]
    public function testShallowVsDeepSearch(string $html, string $tag, bool $deep, int $expectedCount): void
    {
        $document = Document::loadHtml($html);
        $result = $document->query($tag, function (Query $q): void {}, $deep)->get();

        $this->assertCount($expectedCount, $result);
    }

    #[DataProvider('malformedHtmlProvider')]
    public function testMalformedHtmlHandling(string $html, string $tag, int $minExpectedCount): void
    {
        $document = Document::loadHtml($html);
        $result = $document->query($tag, function (Query $q): void {}, true)->get();

        $this->assertGreaterThanOrEqual($minExpectedCount, $result->count());
    }

    public static function searchModeProvider(): \Generator
    {
        yield 'shallow div search - none found' => [
            '<html><body><section><div>Content</div></section></body></html>',
            'div',
            false,
            0,
        ];
        yield 'deep div search - one found' => [
            '<html><body><section><div>Content</div></section></body></html>',
            'div',
            true,
            1,
        ];
        yield 'shallow p search - direct children only' => [
            '<html><body><p>Direct</p><div><p>Nested</p></div></body></html>',
            'p',
            false,
            0,
        ];
        yield 'deep p search - all descendants' => [
            '<html><body><p>Direct</p><div><p>Nested</p></div></body></html>',
            'p',
            true,
            2,
        ];
        yield 'html5 article shallow vs deep' => [
            '<html><body><main><article>Content</article></main></body></html>',
            'article',
            false,
            0,
        ];
        yield 'html5 article deep search' => [
            '<html><body><main><article>Content</article></main></body></html>',
            'article',
            true,
            1,
        ];
    }

    public static function malformedHtmlProvider(): \Generator
    {
        yield 'unclosed tags' => [
            '<html><body><div><p>Unclosed paragraph<div>Another div</div></body></html>',
            'div',
            2,
        ];
        yield 'mismatched tags' => [
            '<html><body><span><div>Mixed</span></div><p>Text</p></body></html>',
            'p',
            1,
        ];
        yield 'missing quotes in attributes' => [
            '<html><body><div class=unquoted id="proper">Content</div></body></html>',
            'div',
            1,
        ];
    }

    #[DataProvider('complexSubqueryProvider')]
    public function testComplexSubqueries(string $html, callable $queryBuilder, int $expectedCount, array $expectedTexts): void
    {
        $document = Document::loadHtml($html);
        $results = $queryBuilder($document);

        $this->assertCount($expectedCount, $results);

        foreach ($expectedTexts as $index => $expectedText) {
            $this->assertEquals($expectedText, $results->get($index)->text());
        }
    }

    #[DataProvider('advancedLogicProvider')]
    public function testAdvancedLogicQueries(string $html, callable $queryBuilder, array $expectedResults): void
    {
        $document = Document::loadHtml($html);
        $results = $queryBuilder($document);

        $this->assertCount(count($expectedResults), $results);

        foreach ($expectedResults as $index => $expected) {
            $element = $results->get($index);

            foreach ($expected as $property => $value) {
                $this->assertEquals($value, $element->$property());
            }
        }
    }

    public static function complexSubqueryProvider(): \Generator
    {
        yield 'nested form validation with subqueries' => [
            '<html><body>
                <form class="user-form" data-type="registration">
                    <div class="field-group required">
                        <input type="email" name="email" required class="validated" />
                    </div>
                    <div class="field-group optional">
                        <input type="password" name="password" class="validated" />
                    </div>
                    <div class="field-group required">
                        <input type="text" name="username" required />
                    </div>
                    <button type="submit" class="btn primary disabled">Register</button>
                </form>
            </body></html>',
            function (Document $doc) {
                return $doc->query('div', function (Query $q): void {
                    $q->where('class', 'contains', 'field-group')
                        ->where(function (Query $subQuery): void {
                            $subQuery->where('class', 'contains', 'required')
                                ->orWhere('class', 'contains', 'optional');
                        });
                }, true)->get();
            },
            3,
            ['', '', ''],
        ];
        yield 'multi-condition article filtering' => [
            '<html><body>
                <article class="post published featured" data-category="tech" data-priority="high">
                    <h2>Featured Tech</h2>
                </article>
                <article class="post published" data-category="news" data-priority="normal">
                    <h2>News Article</h2>
                </article>
                <article class="post draft" data-category="tech" data-priority="high">
                    <h2>Draft Tech</h2>
                </article>
                <article class="post published" data-category="tech" data-priority="low">
                    <h2>Low Priority Tech</h2>
                </article>
            </body></html>',
            function (Document $doc) {
                return $doc->query('article', function (Query $q): void {
                    $q->where('class', 'contains', 'published')
                        ->where(function (Query $categoryAndPriority): void {
                            $categoryAndPriority->where('data-category', 'tech')
                                ->where(function (Query $priorityCheck): void {
                                    $priorityCheck->where('data-priority', 'high')
                                        ->orWhere('class', 'contains', 'featured');
                                });
                        });
                }, true)->get();
            },
            1,
            ['Featured Tech'],
        ];
    }

    public static function advancedLogicProvider(): \Generator
    {
        yield 'multi-level product filtering' => [
            '<html><body>
                <div class="product featured available" data-price="99" data-rating="5">
                    <h3>Premium Product</h3>
                    <span class="category">electronics</span>
                </div>
                <div class="product" data-price="49" data-rating="4">
                    <h3>Budget Product</h3>
                    <span class="category">electronics</span>
                </div>
                <div class="product featured sale available" data-price="79" data-rating="5">
                    <h3>Sale Product</h3>
                    <span class="category">books</span>
                </div>
                <div class="product" data-price="29" data-rating="3">
                    <h3>Cheap Product</h3>
                    <span class="category">books</span>
                </div>
            </body></html>',
            function (Document $doc) {
                return $doc->query('div', function (Query $q): void {
                    $q->where('class', 'contains', 'product')
                        ->where(function (Query $priceAndRating): void {
                            $priceAndRating->where('data-rating', '5')
                                ->orWhere('class', 'contains', 'featured');
                        })
                        ->where(function (Query $availability): void {
                            $availability->where('class', 'contains', 'available')
                                ->orWhere('class', 'contains', 'sale');
                        });
                }, true)->get();
            },
            [
                ['tagName' => 'div'],
                ['tagName' => 'div'],
            ],
        ];
        yield 'user permissions complex query' => [
            '<html><body>
                <div class="user admin active" data-role="administrator" data-permissions="all">
                    <span class="name">Admin User</span>
                </div>
                <div class="user moderator active" data-role="moderator" data-permissions="moderate">
                    <span class="name">Mod User</span>
                </div>
                <div class="user member inactive" data-role="member" data-permissions="read">
                    <span class="name">Regular User</span>
                </div>
                <div class="user vip active" data-role="member" data-permissions="read,write">
                    <span class="name">VIP User</span>
                </div>
            </body></html>',
            function (Document $doc) {
                return $doc->query('div', function (Query $q): void {
                    $q->where('class', 'contains', 'user')
                        ->where('class', 'contains', 'active')
                        ->where(function (Query $permissions): void {
                            $permissions->where(function (Query $adminOrMod): void {
                                $adminOrMod->where('data-role', 'administrator')
                                    ->orWhere('data-role', 'moderator');
                            })
                                ->orWhere(function (Query $specialMember): void {
                                    $specialMember->where('data-role', 'member')
                                        ->where(function (Query $vipConditions): void {
                                            $vipConditions->where('class', 'contains', 'vip')
                                                ->orWhere('data-permissions', 'contains', 'write');
                                        });
                                });
                        });
                }, true)->get();
            },
            [
                ['tagName' => 'div'],
                ['tagName' => 'div'],
                ['tagName' => 'div'],
            ],
        ];
    }

    #[DataProvider('multiLevelQueryProvider')]
    public function testMultiLevelQueries(string $html, callable $queryBuilder, array $expectedResults): void
    {
        $document = Document::loadHtml($html);
        $results = $queryBuilder($document);

        $this->assertCount(count($expectedResults), $results);

        foreach ($expectedResults as $index => $expected) {
            $element = $results->get($index);

            foreach ($expected as $property => $value) {
                $this->assertEquals($value, $element->$property());
            }
        }
    }

    public static function multiLevelQueryProvider(): \Generator
    {
        yield 'div -> p -> a navigation' => [
            '<html><body>
                <div class="content">
                    <p class="intro">Welcome to our <a href="/home" class="nav-link">homepage</a></p>
                    <p class="text">Regular paragraph</p>
                    <span>Not a paragraph</span>
                </div>
                <div class="sidebar">
                    <p class="info">Check our <a href="/about" class="nav-link">about page</a></p>
                </div>
                <section>
                    <p>Outside content with <a href="/external">external link</a></p>
                </section>
            </body></html>',
            function (Document $doc) {
                return $doc->query('div', function (Query $q): void {
                    $q->where('class', 'contains', 'content')
                        ->orWhere('class', 'contains', 'sidebar');
                }, true)
                    ->query('p', function (Query $q): void {}, true)
                    ->query('a', function (Query $q): void {
                        $q->where('class', 'nav-link');
                    }, true)
                    ->get();
            },
            [
                ['text' => 'homepage', 'tagName' => 'a'],
                ['text' => 'about page', 'tagName' => 'a'],
            ],
        ];
        yield 'article -> section -> header -> h1 hierarchy' => [
            '<html><body>
                <article class="post">
                    <section class="main">
                        <header class="title-area">
                            <h1 class="main-title">Main Article</h1>
                            <h2>Subtitle</h2>
                        </header>
                        <div class="content">Content here</div>
                    </section>
                    <section class="comments">
                        <header>
                            <h1 class="comments-title">Comments</h1>
                        </header>
                    </section>
                </article>
                <aside>
                    <section>
                        <header>
                            <h1>Sidebar Title</h1>
                        </header>
                    </section>
                </aside>
            </body></html>',
            function (Document $doc) {
                return $doc->query('article', function (Query $q): void {
                    $q->where('class', 'post');
                }, true)
                    ->query('section', function (Query $q): void {}, true)
                    ->query('header', function (Query $q): void {}, true)
                    ->query('h1', function (Query $q): void {}, true)
                    ->get();
            },
            [
                ['text' => 'Main Article', 'tagName' => 'h1'],
                ['text' => 'Comments', 'tagName' => 'h1'],
            ],
        ];
        yield 'form -> fieldset -> div -> input deep nesting' => [
            '<html><body>
                <form class="user-form">
                    <fieldset class="personal-info">
                        <div class="field-wrapper required">
                            <input type="text" name="name" required />
                        </div>
                        <div class="field-wrapper optional">
                            <input type="email" name="email" />
                        </div>
                    </fieldset>
                    <fieldset class="preferences">
                        <div class="field-wrapper">
                            <input type="checkbox" name="newsletter" />
                        </div>
                    </fieldset>
                </form>
                <div class="other-form">
                    <input type="text" name="search" />
                </div>
            </body></html>',
            function (Document $doc) {
                return $doc->query('form', function (Query $q): void {
                    $q->where('class', 'user-form');
                }, true)
                    ->query('fieldset', function (Query $q): void {}, true)
                    ->query('div', function (Query $q): void {
                        $q->where('class', 'contains', 'field-wrapper');
                    }, true)
                    ->query('input', function (Query $q): void {}, true)
                    ->get();
            },
            [
                ['tagName' => 'input'],
                ['tagName' => 'input'],
                ['tagName' => 'input'],
            ],
        ];
    }
}

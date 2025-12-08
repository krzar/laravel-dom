# Laravel DOM
![GitHub License](https://img.shields.io/github/license/krzar/laravel-dom)
![GitHub Release](https://img.shields.io/github/v/release/krzar/laravel-dom?cacheSeconds=300)
![GitHub last commit](https://img.shields.io/github/last-commit/krzar/laravel-dom?cacheSeconds=300)
![GitHub branch check runs](https://img.shields.io/github/check-runs/krzar/laravel-dom/main?cacheSeconds=300)

Package allows you to query, create, update and delete DOM Documents in Laravel style
using functions like `where`, `orWhere`, `whereHas` etc.

You can get the first queried Element/Node or Collection of Elements/Nodes.

## Requirements

- Laravel 12+
- PHP 8.2+ (with **ext-dom**)

## Installation

```bash
composer require krzar/laravel-dom
```

## Documentation

Look here for every class detailed documentation, also to see how to add and manipulate DOM elements: [Documentation](docs/index.md)

## Examples

### Create new Document

```php
use KrZar\LaravelDom\Document;

$htmlDocument = Document::loadHtml($html);
$xmlDocument = Document::loadXml($xml);
```

### Basic query example

```php
use KrZar\LaravelDom\Document;
use KrZar\LaravelDom\Query\Query;

$document = Document::loadHtml($html);
$elements = $document->query('div', function(Query $query) {
    $query->where('class', 'searched-class');
})->get();
```

### Available selectors

#### equals (default)

```php
use KrZar\LaravelDom\Document;
use KrZar\LaravelDom\Query\Query;

$document = Document::loadHtml($html);
$elements = $document->query('div', function(Query $query) {
    $query->where('class', '=', 'searched-class');
})->get();

// OR

$elements = $document->query('div', function(Query $query) {
    $query->whereEquals('class', 'searched-class');
})->get();
```

#### contains

```php
use KrZar\LaravelDom\Document;
use KrZar\LaravelDom\Query\Query;

$document = Document::loadHtml($html);
$elements = $document->query('div', function(Query $query) {
    $query->where('class', 'contains', 'searched-class');
})->get();

// OR

$elements = $document->query('div', function(Query $query) {
    $query->whereContains('class', 'searched-class');
})->get();

```

#### not equals

```php
use KrZar\LaravelDom\Document;
use KrZar\LaravelDom\Query\Query;

$document = Document::loadHtml($html);
$elements = $document->query('div', function(Query $query) {
    $query->where('class', '!=', 'searched-class');
})->get();

// OR

$elements = $document->query('div', function(Query $query) {
    $query->whereNotEquals('class', 'searched-class');
})->get();
```

#### not contains

```php
use KrZar\LaravelDom\Document;
use KrZar\LaravelDom\Query\Query;

$document = Document::loadHtml($html);
$elements = $document->query('div', function(Query $query) {
    $query->where('class', '!contains', 'searched-class');
})->get();

// OR

$elements = $document->query('div', function(Query $query) {
    $query->whereNotContains('class', 'searched-class');
})->get();
```

#### has

```php
use KrZar\LaravelDom\Document;
use KrZar\LaravelDom\Query\Query;

$document = Document::loadHtml($html);
$elements = $document->query('div', function(Query $query) {
    $query->whereHas('id');
})->get();
```

You can also use `where('id', 'has')`

#### not has

```php
use KrZar\LaravelDom\Document;
use KrZar\LaravelDom\Query\Query;

$document = Document::loadHtml($html);
$elements = $document->query('div', function(Query $query) {
    $query->whereNotHas('id');
})->get();
```

You can also use `where('id', '!has')`

### Advanced examples

#### orWhere

```php
use KrZar\LaravelDom\Document;
use KrZar\LaravelDom\Query\Query;

$document = Document::loadHtml($html);
$elements = $document->query('div', function(Query $query) {
    $query->where('class', 'contains', 'searched-class')
        ->orWhereHas('id');
})->query('a', function(Query $query) {
    $query->whereHas('href')
        ->whereContains('class', 'link');
})->get();
```

#### deep search

By default, a query is not searching deep inside DOM; it looks only for first children (XPath **/**).

To search deep for any child (XPath **//**) you need to add true on the end of `query`.

```php
use KrZar\LaravelDom\Document;
use KrZar\LaravelDom\Query\Query;

$document = Document::loadHtml($html);
$elements = $document->query('a', function(Query $query) {
    $query->where('class', 'searched-class');
}, true)->get();
```

You can also use `queryDeep` method.

```php
use KrZar\LaravelDom\Document;
use KrZar\LaravelDom\Query\Query;

$document = Document::loadHtml($html);
$elements = $document->queryDeep('a', function(Query $query) {
    $query->where('class', 'searched-class');
})->get();
```

#### Look for any

You can also look for any element just using `*`

```php
use KrZar\LaravelDom\Document;
use KrZar\LaravelDom\Query\Query;

$document = Document::loadHtml($html);
$elements = $document->query('*', function(Query $query) {
    $query->where('class', 'searched-class');
})->get();
```

#### Nested conditions

If you want to search for condition like `a || (b && c)`. You can make it using subqueries.

```php
use KrZar\LaravelDom\Document;
use KrZar\LaravelDom\Query\Query;

$document = Document::loadHtml($html);
$elements = $document->query('span', function(Query $query) {
    $query->whereContains('class', 'searched-class')
        ->orWhere(function (Query $subQuery) {
            $subQuery->whereNotContains('class', 'another-class')
                ->whereEquals('title', 'some-title');
        });
})->get();
```

#### Query text content

You can also query text content of an element.

```php
use KrZar\LaravelDom\Document;
use KrZar\LaravelDom\Query\Query;

$document = Document::loadHtml($html);
$elements = $document->query('div', function(Query $query) {
    $query->where('class', 'contains', 'searched-class')
        ->whereText('Some text');
}
)->get();
```

##### Available text methods

- `whereText`
- `whereTextEquals`
- `whereTextNotEquals`
- `whereTextContains`
- `whereTextNotContains`
- `orWhereText`
- `orWhereTextEquals`
- `orWhereTextNotEquals`
- `orWhereTextContains`
- `orWhereTextNotContains`

By default these methods search only for text content of an element, not for element children.

To search deep for any child (XPath **//**) you need to add true on the end of the method.

For example:

```php
use KrZar\LaravelDom\Document;
use KrZar\LaravelDom\Query\Query;

$document = Document::loadHtml($html);
$elements = $document->query('div', function(Query $query) {
    $query->where('class', 'contains', 'searched-class')
        ->whereTextContains('Some text', true);
}
)->get();
```

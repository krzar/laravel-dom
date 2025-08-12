# Laravel DOM
![license mit](https://badgen.net/github/license/krzar/laravel-dom)
![release](https://badgen.net/github/release/krzar/laravel-dom/main)
![last commit](https://badgen.net/github/last-commit/krzar/laravel-dom)

Package allows you to query DOM Documents in Laravel style
using functions like `where`, `orWhere`, `whereHas` etc.

You can get the first queried Element/Node or Collection of Elements/Nodes.

For now, you can only read details about a given Element / Node,
but still you can get PHP Native classes and manipulate with them for now.

#### Upcoming features

- Set Elements/Nodes attributes
- Inserting Elements/Nodes
- Updating Elements/Nodes
- Deleting Elements/Nodes

## Requirements

- Laravel 12+
- PHP 8.2+ (with **ext-dom**)

## Installation

```bash
composer require krzar/laravel-dom
```

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
```

#### contains

```php
use KrZar\LaravelDom\Document;
use KrZar\LaravelDom\Query\Query;

$document = Document::loadHtml($html);
$elements = $document->query('div', function(Query $query) {
    $query->where('class', 'contains', 'searched-class');
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
```

#### not contains

```php
use KrZar\LaravelDom\Document;
use KrZar\LaravelDom\Query\Query;

$document = Document::loadHtml($html);
$elements = $document->query('div', function(Query $query) {
    $query->where('class', '!contains', 'searched-class');
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
        ->where('class', 'link');
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
    $query->where('class', 'searched-class')
        ->orWhere(function (Query $subQuery) {
            $subQuery->where('class', 'another-class')
                ->where('title', 'some-title');
        });
})->get();
```
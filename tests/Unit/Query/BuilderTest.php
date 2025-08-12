<?php

declare(strict_types=1);

use KrZar\LaravelDom\Document;
use KrZar\LaravelDom\Query\Query;
use PHPUnit\Framework\TestCase;

class BuilderTest extends TestCase
{
    public function testQuery()
    {
        $document = Document::loadHtml('<html></html>');

        $result = $document->query('a', function (Query $builder) {
           $builder->where('href', 'contains', 'test')
               ->orWhere('class', '!=', 'link')
               ->whereHas('id');
        })->query('span', function (Query $builder) {
            $builder->where('class', 'link-text')
                ->orWhereHas('id');
        }, true)
            ->query('p', function (Query $builder) {
                $builder->whereText('Main title', deep: true)
                    ->orWhere(function (Query $query) {
                       $query->where('class', 'link-text')
                           ->where('title', 'contains', 'test');
                    });
            })
            ->queryString();

        var_dump($result); die();
    }
}
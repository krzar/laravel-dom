<?php

declare(strict_types=1);

namespace KrZar\LaravelDom\Query;

enum Operator: string
{
    case EQUALS = '=';
    case NOT_EQUALS = '!=';
    case CONTAINS = 'contains';
    case NOT_CONTAINS = '!contains';
    case HAS = 'has';
    case NOT_HAS = '!has';

    public function isValueRequired(): bool
    {
        return ! in_array($this, [self::HAS, self::NOT_HAS]);
    }
}

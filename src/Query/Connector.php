<?php

declare(strict_types=1);

namespace KrZar\LaravelDom\Query;

enum Connector: string
{
    case AND = 'and';
    case OR = 'or';
}

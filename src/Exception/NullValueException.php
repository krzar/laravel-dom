<?php

declare(strict_types=1);

namespace KrZar\LaravelDom\Exception;

use KrZar\LaravelDom\Query\Operator;

class NullValueException extends \Exception
{
    public function __construct(Operator $operator)
    {
        parent::__construct(
            sprintf('Value for query with operator %s is required.', $operator->value)
        );
    }
}

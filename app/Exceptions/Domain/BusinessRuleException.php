<?php

namespace App\Exceptions\Domain;

use RuntimeException;

class BusinessRuleException extends RuntimeException
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}

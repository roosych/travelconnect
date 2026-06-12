<?php

namespace App\Exceptions\Domain;

use RuntimeException;

class InvalidStatusTransitionException extends RuntimeException
{
    public function __construct(string $entity, string $from, string $to)
    {
        parent::__construct(
            "Cannot transition {$entity} from '{$from}' to '{$to}'."
        );
    }
}

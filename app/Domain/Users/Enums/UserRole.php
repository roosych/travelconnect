<?php

namespace App\Domain\Users\Enums;

enum UserRole: string
{
    case Agency   = 'agency';
    case Operator = 'operator';
    case Supplier = 'supplier';
}

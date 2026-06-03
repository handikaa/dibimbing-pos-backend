<?php

namespace App\Domain\CashierSession\Exceptions;

use Exception;

class SessionNotFoundException extends Exception
{
    public function __construct(string $message = 'Cashier session not found.')
    {
        parent::__construct($message);
    }
}
<?php

namespace App\Domain\CashierSession\Exceptions;

use Exception;

class InvalidSessionStatusException extends Exception
{
    public function __construct(string $message = 'Invalid session status.')
    {
        parent::__construct($message);
    }
}
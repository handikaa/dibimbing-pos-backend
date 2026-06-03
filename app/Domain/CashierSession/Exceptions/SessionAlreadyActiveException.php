<?php

namespace App\Domain\CashierSession\Exceptions;

use Exception;

class SessionAlreadyActiveException extends Exception
{
    public function __construct(string $message = 'You already have an active session. Please close it first.')
    {
        parent::__construct($message);
    }
}
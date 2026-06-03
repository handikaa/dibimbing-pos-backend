<?php

namespace App\Domain\User\Exceptions;

use Exception;

class InvalidCredentialsException extends Exception
{
    public function __construct(string $message = 'Email atau password salah.')
    {
        parent::__construct($message);
    }
}
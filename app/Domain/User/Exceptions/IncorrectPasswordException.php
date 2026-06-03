<?php

namespace App\Domain\User\Exceptions;

use Exception;

class IncorrectPasswordException extends Exception
{
    public function __construct(string $message = 'Password saat ini tidak sesuai.')
    {
        parent::__construct($message);
    }
}
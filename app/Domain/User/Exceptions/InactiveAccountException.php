<?php

namespace App\Domain\User\Exceptions;

use Exception;

class InactiveAccountException extends Exception
{
    public function __construct(string $message = 'Akun Anda tidak aktif. Hubungi administrator.')
    {
        parent::__construct($message);
    }
}
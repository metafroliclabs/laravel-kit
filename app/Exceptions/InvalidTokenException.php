<?php

namespace App\Exceptions;

use Exception;

class InvalidTokenException extends Exception
{
    public function __construct($message = "Invalid code, please try again.")
    {
        parent::__construct($message);
    }
}

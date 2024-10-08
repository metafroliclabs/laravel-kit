<?php

namespace App\Exceptions;

use Exception;

class BadRequestException extends Exception
{
    public function __construct($message = "Bad request")
    {
        parent::__construct($message);
    }
}

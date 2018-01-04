<?php

namespace DialInno\Jaal\Exceptions;

class ValidationException extends Exception
{
    public static function make()
    {
        return new static;
    }
}

<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Support\MessageBag;
use Throwable;

class MessageBagException extends Exception
{
    public function __construct(MessageBag $errors, $message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->errors = $errors;
    }

    protected $errors;

    public function getErrors(){
        return $this->errors;
    }
}

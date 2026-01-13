<?php

namespace App\Exceptions;

use Exception;
use Throwable;

class AccountAuthorizeException extends Exception
{
    public function __construct($message = "", $code = 0, Throwable $previous = null, $responseData = [], $responseCode = 500)
    {
        parent::__construct($message, $code, $previous);
        $this->responseData = $responseData;
        $this->responseCode = $responseCode;
    }

    protected $responseData;
    public function getResponseData(){
        return $this->responseData;
    }

    protected $responseCode;
    public function getResponseCode(){
        return $this->responseCode;
    }

}

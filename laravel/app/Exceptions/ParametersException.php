<?php


namespace App\Exceptions;


use App\util\ResponseCode;
use Exception;

class ParametersException extends Exception
{

    /**
     * ParametersException constructor.
     */
    public function __construct($errmsg)
    {
        list($code, $message) = ResponseCode::PARAM_ERROR;
        parent::__construct($errmsg ?: $message, $code);
    }
}

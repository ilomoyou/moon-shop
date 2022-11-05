<?php


namespace App\Exceptions;


use App\util\ResponseCode;
use Exception;

class NotFoundException extends Exception
{

    /**
     * NotFoundException constructor.
     */
    public function __construct($errMsg)
    {
        list($code, $message) = ResponseCode::RESOURCE_NOT_FOUND;
        parent::__construct($errMsg ?: $message, $code);
    }
}

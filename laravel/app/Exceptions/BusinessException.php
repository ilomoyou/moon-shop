<?php

namespace App\Exceptions;

use Exception;

class BusinessException extends Exception
{

    /**
     * BusinessException constructor.
     */
    public function __construct(array $responseCode, $info = '')
    {
        list($code, $message) = $responseCode;
        parent::__construct($info ?: $message, $code);
    }
}

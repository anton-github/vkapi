<?php
namespace vkapi\exceptions;

class AccessDeniedException extends ResponseErrorException
{
    const ERROR_CODE = 15;

    public function __construct($message = "", $code = self::ERROR_CODE)
    {
        parent::__construct($message, $code);
    }
}
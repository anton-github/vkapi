<?php
namespace vkapi\exceptions;

class ResponseErrorException extends VkApiException
{
    public function __construct($message = "", $code = 0)
    {
        parent::__construct($message, $code);
    }
}
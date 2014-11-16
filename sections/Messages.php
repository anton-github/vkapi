<?php
namespace vkapi\sections;

use vkapi\Request;

class Messages extends Section
{
    public static function send()
    {
        return new Request('messages.send');
    }
}
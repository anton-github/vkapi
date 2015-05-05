<?php
namespace vkapi\sections;

use vkapi\Request;

class Secure extends Section
{
    public static function setCounter()
    {
        return new Request('secure.setCounter');
    }
}
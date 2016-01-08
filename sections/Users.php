<?php
namespace vkapi\sections;

use vkapi\Request;

class Users extends Section
{
    public static function get()
    {
        return new Request('users.get');
    }
}

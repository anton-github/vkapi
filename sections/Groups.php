<?php
namespace vkapi\sections;

use vkapi\Request;

class Groups extends Section
{
    public static function getById()
    {
        return new Request('groups.getById');
    }

    public static function join()
    {
        return new Request('groups.join');
    }
}
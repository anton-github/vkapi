<?php
namespace vkapi\sections;

use vkapi\Request;

class Wall extends Section
{
    public static function get()
    {
        return new Request('wall.get');
    }

    public static function getComments()
    {
        return new Request('wall.getComments');
    }
}
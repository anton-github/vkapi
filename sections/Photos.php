<?php
namespace vkapi\sections;

use vkapi\Request;

class Photos extends Section
{
    public static function getAll()
    {
        return new Request('photos.getAll');
    }

    public static function getComments()
    {
        return new Request('photos.getComments');
    }
}
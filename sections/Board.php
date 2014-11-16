<?php
namespace vkapi\sections;

use vkapi\Request;

class Board extends Section
{
    public static function getTopics()
    {
        return new Request('board.getTopics');
    }

    public static function getComments()
    {
        return new Request('board.getComments');
    }
}
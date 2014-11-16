<?php
namespace vkapi\sections;

use vkapi\Request;

class Newsfeed extends Section
{
    public static function getMentions()
    {
        return new Request('newsfeed.getMentions');
    }
}
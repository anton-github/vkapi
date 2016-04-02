<?php
namespace vkapi\sections;

use vkapi\Request;

class Database extends Section
{
    public static function getCountries()
    {
        return new Request('database.getCountries');
    }

    public static function getCities()
    {
        return new Request('database.getCities');
    }
    
    public static function getCitiesById()
    {
        return new Request('database.getCitiesById');
    }
    
    public static function getCountriesById()
    {
        return new Request('database.getCountriesById');
    }
}

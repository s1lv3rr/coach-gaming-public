<?php
namespace App\DataFixtures\Faker;
class UserRoleProvider extends \Faker\Provider\Base{
    protected static $roles = [
        'admin',
        'coach',
        'user'
    ];
    protected static $rolesCodes = [
        'ROLE_ADMIN',
        'ROLE_COACH',
        'ROLE_USER'
    ];


    protected static $gameName = [
        'FIFA 19',
        'PUBG',
        'Overwatch',
        'Super Smash Bros Ultimate'
    ];
    

    protected static $platformName = [
        'PC',
        'PS4',
        'Switch',
        'Xbox One'
    ];

    protected static $teamName = [
        'Vitality',
        'Rogue',
        'Gigantti',
        'Team4',
        'Millenium'
    ];


    public static function userRole(){
        return static::randomElement(static::$roles);
    }
    public static function userRoleCode(){
        return static::randomElement(static::$rolesCodes);
    }

    public static function getGameName(){
        return static::randomElement(static::$gameName);
    }
    

    public static function platformName(){
        return static::randomElement(static::$platformName);
    }

    public static function getTeamName(){
        return static::randomElement(static::$teamName);
    }
}
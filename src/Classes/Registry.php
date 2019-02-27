<?php
/**
 * Created by PhpStorm.
 * User: GARUBA
 * Date: 2/27/2019
 * Time: 1:20 PM
 */

namespace App\Classes;

use \App\Exceptions\PropertyNotRegisteredException;
class Registry
{
    protected static $registry = array();

    public static function register($name, \Closure $resolve) {
        self::$registry[$name] = $resolve;
    }

    public static function run($name) {
        try{
            if (!empty(self::$registry[$name])){
                $func = self::$registry[$name];

                return $func();
            }

            throw new PropertyNotRegisteredException("Property \"{$name}\" doesn't exist on registry");
        }catch(PropertyNotRegisteredException $e){
            echo $e->getMessage();
        }
    }
}
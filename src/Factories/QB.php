<?php
/**
 * Created by PhpStorm.
 * User: GARUBA
 * Date: 2/23/2019
 * Time: 10:09 PM
 */

namespace App\Factories;

use App\Classes\QueryBuilder;

class QB
{
    public static function table($tblName){
        if (!empty($tblName))
            return new QueryBuilder($tblName);
    }

    public static function registerEvent(string $eventType, string $table, callable $callback){
        QueryBuilder::registerEvent($eventType, $table, $callback);
    }
    public static function raw($param){
        QueryBuilder::raw($param);
    }
}
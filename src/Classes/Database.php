<?php

/**
* Created by PhpStorm.
* User: GARUBA
* Date: 2/5/2019
* Time: 6:39 PM
*/
namespace App\Classes;
class Database{
    public static function registerDatabase(){
        Registry::register("pdo", function (){
            try {
                // Init Variables
                $server = getenv('DB_SERVER');
                $host = getenv('DB_HOST');
                $user = getenv('DB_USER');
                $password = getenv('DB_PASS');
                $schema = getenv('DB_DATABASE');

                // Init PDO
                $pdo = new \PDO("$server:host=$host; dbname=$schema", $user, $password);
                $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
                $pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);

                return $pdo;

            }catch(\PDOException $e){
                echo $e->getMessage();
            }
        });
    }
}
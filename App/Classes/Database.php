<?php

    use App\Classes\Database;    

    namespace App\Classes {
        class Database {

            private $_pdo;
            private static $instance;
            
            public  function __construct(){
                try { 
                    // Init Variables 
                    $server = getenv('DB_SERVER');
                    $host = getenv('DB_HOST');
                    $user = getenv('DB_USER');
                    $password = getenv('DB_PASS');
                    $schema = getenv('DB_DATABASE');

                    // Init PDO
                    self::$instance = new \PDO("$server:host=$host; dbname=$schema", $user, $password);
                    
                    self::$instance->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
                }catch(\PDOException $e){
                    echo $e->getMessage();
                }
            }

            public static function getInstance(){
                
                if (!isset($instance)){
                    self::$instance = new Database();
                }
                return self::$instance;
            }

        }
    }
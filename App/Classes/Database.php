<?php

        /**
         * Created by PhpStorm.
         * User: GARUBA
         * Date: 2/5/2019
         * Time: 6:39 PM
         */
        namespace App\Classes;
        class Database {


            private static $instance;
            private $_pdo;

            private  function __construct(){
                try { 
                    // Init Variables 
                    $server = getenv('DB_SERVER');
                    $host = getenv('DB_HOST');
                    $user = getenv('DB_USER');
                    $password = getenv('DB_PASS');
                    $schema = getenv('DB_DATABASE');

                    // Init PDO
                    $this->_pdo = new \PDO("$server:host=$host; dbname=$schema", $user, $password);
                    $this->_pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
                }catch(\PDOException $e){
                    echo $e->getMessage();
                }
            }
            
            //Return instance of the class
            public static function getInstance(){
                
                if (!isset($instance)){
                    self::$instance = new Database();
                }
                
                return self::$instance;
            }

            public function getConnection(){
                return $this->_pdo;
            }
        }
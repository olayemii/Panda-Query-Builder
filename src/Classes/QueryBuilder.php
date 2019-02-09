<?php

        /**
         * Created by PhpStorm.
         * User: GARUBA
         * Date: 2/5/2019
         * Time: 6:39 PM
         */

        namespace App\Classes;
        use App\Classes\Database;
        use App\Exceptions\{
            InvalidArgumentsCountException,
            InvalidArgumentException
        };

        class QueryBuilder {

            private $_dbh;
            private $args = array(
                "TYPE"          =>       [],
                "COLUMNS"       =>       [],
                "WHERE"         =>       [],
                "ORDERBY"       =>       [],
                "GROUPBY"       =>       [],
                "LIMIT"         =>       [],
                "DISTINCT"      =>       false,
                "FROM"          =>       [],
                "INSERT"        =>       [],
                "TEST_ROWS_COUNT"=>      50
            );


            /**
             * QueryBuilder constructor.
             *
             */
            public function __construct($tblName){
                // Initialize $_dbh to hold an instance of the PDO object 
                $this->_dbh = Database::getInstance()->getConnection();

                $this->args['FROM'] = explode(",", $tblName);

            }

            /**
             * @param null|string $logical
             * @param mixed       ...$whereArguments
             *
             *  Sets the SQL WHERE clause arguments and also stores the logical conditions attached to each in an array
             *  Default is AND
             * @return $this
             */
                private function whereBuilder(?string $logical, ...$whereArguments){
                try {
                    switch(count($whereArguments)){
                        case 3:
                        case 2:
                            $this->loadWhere($logical, $whereArguments);
                            break;
                        case 1:
                            if ($this->containsArray($whereArguments[0])){
                                foreach ($whereArguments[0] as $arg){
                                    array_unshift($arg, $logical);
                                    call_user_func_array(array($this, 'whereBuilder'), $arg);
                                }
                            }else{
                                array_unshift($whereArguments, $logical);
                                call_user_func_array(array($this, 'whereBuilder'), $whereArguments);
                            }

                            break;
                        default:
                            throw new InvalidArgumentsCountException("Invalid amount of arguments passed to WHERE");
                     }
                }catch(InvalidArgumentsCountException $e){
                    echo $e->getMessage();
                    die();
                }

                return $this;
            }

            public function loadWhere($logical, $whereArguments){
                if (count($whereArguments) == 2){
                    array_splice($whereArguments, 1, 0, "=");
                }

                $this->args["WHERE"][] = array(
                    "column"    => $whereArguments[0],
                    "operator"  => $whereArguments[1],
                    "value"     => $whereArguments[2],
                    "boolean"   => $logical
                );
            }

            /**
             * @param mixed ...$whereArguments
             * For WHERE clauses with WHERE condition
             * @return $this
             */
            public function orWhere(...$whereArguments){
                array_unshift($whereArguments, "OR");
                call_user_func_array(array($this, 'whereBuilder'), $whereArguments);

                return $this;
            }

            public function where(...$whereArguments){
                array_unshift($whereArguments, "AND");
                call_user_func_array(array($this, 'whereBuilder'), $whereArguments);

                return $this;
            }

            public function count(){

            }

            public function first(){
                $this->args["LIMIT"][] = array("lowerLimit" => 0,"upperLimit" => 1);
                // Suppose to also execute querty
                return $this;
            }

            /**
             * @param string $columns
             *
             * @return $this
             */
            public function pluck(string $columns){
                $this->args["COLUMNS"] = explode(",", $columns);
                return $this;
            }

            /**
             * @param array $array
             *
             * @return bool
             */
            public function containsArray(array $array): bool{
                foreach ($array as $arr){
                    if (is_array($arr)){
                        return true;
                    }
                }
                return false;
            }

            public function orderBy($column, string $order = "ASC"){
                try {
                    if (!(strtoupper($order) == "ASC" || strtoupper($order) == "DESC")){
                        throw new InvalidArgumentException("Invalid argument supplied to OrderBy() method");
                    }
                    $this->args["ORDERBY"] = array (
                        "order"  => strtoupper($order),
                        "column" => $column
                    );

                }catch(InvalidArgumentException $e){
                    echo $e->getMessage();
                    die();
                }

                return $this;
            }

            public function arrayLoadDefault($defaultValue, &$data){
                if (empty($data)){
                    array_push($data, [$defaultValue]);
                }

            }

            public function buildQuery(){

                //Loading default values for required query parts
                $this->arrayLoadDefault("SELECT", $this->args["TYPE"]);
                $this->arrayLoadDefault("*", $this->args["COLUMNS"]);
            }


            public function groupBy($column){
                $this->args["GROUPBY"] = $column;
                return $this;
            }

            public function distinct(){
                $this->args["DISTINCT"] = true;
                return $this;
            }


            public function chunk(int $limit, $iterFunc){
                for ($i = 0; $i < ceil($this->args['TEST_ROWS_COUNT']/$limit); $i++){
                    if ($i == 0 ){
                        $lowerLimit = 0;
                    }else{
                        $lowerLimit = $limit * $i;
                    }
                    $upperLimit = $limit + ($limit * $i);
                    $this->args["LIMIT"][] = array(
                        "lowerLimit" => $lowerLimit,
                        "upperLimit" => $upperLimit
                    );
                }
            }

            public function insert($insertArray){
                $this->args["TYPE"] = "INSERT";
                if ($this->containsArray($insertArray)){
                    foreach ($insertArray as $key => $insert){
                        $this->insert($insert);
                    }
                }else{
                    foreach ($insertArray as $key => $value){
                        $this->args["INSERT"][] = array(
                            "column" => $key,
                            "value"  => $value
                        );
                    }
                }

                return $this->args["INSERT"];
            }

            public function update(array $updateArguments){
                $this->args["TYPE"] = "UPDATE";
                foreach ($updateArguments as $key => $value){
                    $this->args["UPDATE"][] = array(
                        "column"    => $key,
                        "value"     => $value
                    );
                }

                // To execute query
            }

            public function increment(string $row, int $increment = 1){
                $this->update([$row => $increment]);
            }

            public function decrement(string $row, int $increment = 1){
                $this->update([$row => $increment]);
            }

            public function insertGetId($insertArray, $col = null){
                $this->insert($insertArray);
                return $this->_dbh->lastInsertId($col);
            }

            public function get(){
//                $this->chunk(10, function($value){
//                });
                $this->buildQuery();

                return $this->args;
            }
        }
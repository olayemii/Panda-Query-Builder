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
            InvalidArgumentException,
            CreatingRecordError
        };

        class QueryBuilder {

            private $_dbh;
            private $_error;
            private $args = array(
                "TABLE"             =>       [],
                "TYPE"              =>       [],
                "COLUMNS"           =>       [],
                "WHERE"             =>       [],
                "ORDERBY"           =>       [],
                "GROUPBY"           =>       [],
                "LIMIT"             =>       [],
                "DISTINCT"          =>       false,
                "FROM"              =>       [],
                "INSERT"            =>       [],
                "JOIN"              =>       [],
                "UPDATE_ARGS"       =>       [],
                "BIND_VALUES"       =>       [],
                "TEST_ROWS_COUNT"   =>       50
            );


            /**
             * QueryBuilder constructor.
             *
             */
            public function __construct($tblName){
                // Initialize $_dbh to hold an instance of the PDO object 
                $this->_dbh = Database::getInstance()->getConnection();

                $this->args['TABLE'] = $tblName;

            }


            /**
             * @param null|string $logical
             * @param mixed       ...$whereArguments
             *
             * @return $this
             *
             */

            private function whereBuilder(?string $logical, ...$whereArguments){
                try {
                    switch(count($whereArguments)){
                        case 3:
                        case 2:
                            $this->loadWhere($logical, $whereArguments);
                            break;
                        case 1:
                            if (!$this->containsArray($whereArguments[0])){
                                throw new InvalidArgumentsCountException("Conditions are to be specified in a multi-dimensional array");
                            }else{
                                foreach ($whereArguments[0] as $key => $whereArgArr){
                                    array_unshift($whereArgArr, $logical);
                                    call_user_func_array(array($this, 'whereBuilder'), $whereArgArr);
                                }
                            }

                            break;
                        default:
                            throw new InvalidArgumentsCountException("Invalid amount of arguments passed to WHERE");
                     }
                }catch(InvalidArgumentsCountException $e){
                    $this->_error =  $e->getMessage();
                    die($e->getMessage());
                }

                return $this;

            }

            /**
             * @param array $array
             *
             * @return bool
             *
             */
            private function containsArray(array $array): bool{
                foreach ($array as $arr){
                    if (is_array($arr)){
                        return true;
                    }
                }
                return false;
            }


            /**
             * @return string|void
             *
             */
            private function getConditionals(){
                $whereStr = '';
                if (empty($this->args["WHERE"]))
                    return;
                foreach ($this->args["WHERE"] as $argSetKey => $argSetValue){
                    $whereStr .= $argSetValue["column"] ." ". $argSetValue["operator"]." ". "?";
                    //Loading bind array in same order placeholders where created

                    $this->args["BIND_VALUES"][] = $argSetValue['value'];
                    if(isset($this->args["WHERE"][$argSetKey+1])){
                        $whereStr .= " ". $this->args["WHERE"][$argSetKey+1]["boolean"] ." ";
                    }

                }
                return $whereStr;
            }


            /**
             * @param $logical
             * @param $whereArguments
             */
            private function loadWhere($logical, $whereArguments){
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
             * @param $defaultValue
             * @param $data
             *
             */
            private function arrayLoadDefault($defaultValue, &$data){
                if (empty($data)){
                    $data = $defaultValue;
                }

            }

            /**
             * @return string
             */
            private function buildQuery(){

                try {
                    //Loading default values for required query parts
                    $sql = '';
                    //                $this->arrayLoadDefault("SELECT", $this->args["TYPE"]);
                    //                $this->arrayLoadDefault(["column" => "*"], $this->args["COLUMNS"][]);
                    if (empty($this->args["TYPE"])){
                        throw new \Exception("You must specify a Data Manipulation method (select, update, delete, insert)");
                    }
                    $type = $this->args["TYPE"];

                    $columnArray = isset($this->args["COLUMNS"]) && $this->args["COLUMNS"];
                    if (isset($columnArray) && !empty($columnArray)){
                        $columns = implode(",",array_values(array_column($this->args["COLUMNS"], "column")));
                        $values = implode(",",array_values(array_column($this->args["COLUMNS"], "value")));
                    }

                    $columns = $columns??"";
                    $table = $this->args["TABLE"];
                    $limit = implode(",", $this->args["LIMIT"]);
                    $conditionals = $this->getConditionals() ?? 1;

                    //This works for SELECT and DELETE
                    switch(explode(" ",$this->args["TYPE"])[0]){
                        case "SELECT":
                        case "DELETE":
                            $sql = $type." ".$columns." FROM ".$table." WHERE ".$conditionals.($limit?" LIMIT $limit ":"");
                            break;
                        case "UPDATE":
                            $updatesArr = [];
                            foreach ($this->args["UPDATE_ARGS"] as $tblColumn => $newValue){
                                $updatesArr[] = $tblColumn . " = ". "?";
                            }
                            $updates = implode(",", $updatesArr);
                            $sql = $type." ".$table." SET ".$updates." WHERE ".$conditionals;

                            break;
                    }
                    return $sql;
                }catch(\Exception $e){
                    die($e->getMessage());
                }
            }



            private function resetArgs(){
                array_walk($this->args, array($this, 'unsetArray'));
            }


            /**
             * @param $args
             * @param $key
             *            Actual implementation of the unsetter
             */
            private function unsetArray(&$args, $key){
                if (!in_array(strtolower($key), ["table", "test_rows_count"]) )
                    unset($this->args[$key]);
            }


            /**
             * @param $query
             * @param $bindParams
             *
             * @return bool
             *
             */
            private function executeQuery($query, $bindParams = null){
                $bindParams = $bindParams ?? array_values($this->args["BIND_VALUES"]);
                try{

                    $stmt = $this->_dbh->prepare($query);
                    $stmt->execute($bindParams);
                    return $stmt;

                }catch(\PDOException $e){
                    die($e->getMessage());
                }
            }

            public function getError(){
                return $this->_error;
            }


            /*********************************************************************************
             *                               Public Methods Available                        *
             *                                                                                *
             *                                                                                *
            ********************************************************************************/

            /**
             * @param $insertArray
             *
             * @return bool
             */
            public function insert($insertArray){
                if ($this->containsArray($insertArray)){
                    foreach ($insertArray as $key => $insert){
                        $this->insert($insert);
                    }
                }else{
                    foreach ($insertArray as $key => $value){
                        $this->args["COLUMNS"][] = array(
                            "column" => $key,
                            "value"  => $value
                        );
                    }
                }

                $columns = implode(",",array_values(array_column($this->args["COLUMNS"], "column")));
                $table = $this->args["TABLE"];

                $values = array_values(array_column($this->args["COLUMNS"], "value"));
                // Placeholders as ?
                $valuesPlaceholder = implode(",",array_fill(0, count($this->args["COLUMNS"]), "?"));

                try{
                    $sql = "INSERT INTO $table ($columns) VALUES($valuesPlaceholder)";
                    $this->_dbh->prepare($sql)->execute($values);
                    $this->resetArgs();
                    return true;
                }catch(\PDOException $e){
                    $this->_error =  $e->getMessage();
                    return false;
                }
            }


            /**
             * @param      $insertArray
             * @param null $col
             *
             * @return string
             */
            public function insertGetId($insertArray, $col = null){
                $this->insert($insertArray);
                return $this->_dbh->lastInsertId($col);
            }


            /**
             * @param mixed ...$columns
             *
             * @return $this
             */
            public function select(...$columns){
                $this->args["TYPE"] = "SELECT";
                if (empty($columns)){
                    array_push($columns, "*");
                }
                foreach ($columns as $col){
                    $this->args["COLUMNS"][] = array(
                        "column" => $col
                    );
                }
                return $this;
            }



            /**
             * @param mixed ...$whereArguments
             * For WHERE clauses with WHERE condition
             * @return $this
             */
            public function orWhere(...$whereArguments){
                array_unshift($whereArguments, "OR");
                call_user_func_array(array($this, 'whereBuilder'), $whereArguments);
                var_dump($this->args["WHERE"]);

                return $this;
            }


            /**
             * @param mixed ...$whereArguments
             *
             * @return $this
             */
            public function where(...$whereArguments){
                array_unshift($whereArguments, "AND");
                call_user_func_array(array($this, 'whereBuilder'), $whereArguments);
                return $this;
            }


            public function count(){
                $this->args["TYPE"] = "SELECT COUNT(*)";
                unset($this->args["COLUMNS"]);
                echo $this->buildQuery();
                return $this->executeQuery($this->buildQuery())->fetchAll();
            }

            /**
             * @return $this
             */
            public function first(){
                $this->args["LIMIT"] = array("upperLimit" => 1);
                // Suppose to also execute query
                return $this->executeQuery($this->buildQuery())->fetchAll();
            }


            /**
             * @param        $column
             * @param string $order
             *
             * @return $this
             */
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
                    $this->_error =  $e->getMessage();
                    die($e->getMessage());
                }

                return $this;
            }




            /**
             * @return bool
             */
            public function delete(){
                $this->args["TYPE"] = "DELETE";
                var_dump(array_values($this->args["BIND_VALUES"]));
                return (bool) $this->executeQuery($this->buildQuery());
            }


            /**
             * @param $column
             *
             * @return $this
             *
             */
            public function groupBy($column){
                $this->args["GROUPBY"] = $column;
                return $this;
            }

            /**
             * @return $this
             */
            public function distinct(){
                $this->args["TYPE"] = "SELECT DISTINCT";
                return $this;
            }


            /**
             * @param int $limit
             * @param     $iterFunc
             *
             */
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





            /**
             * @param array $updateArguments
             *
             * @return bool
             */
            public function update(array $updateArguments){

                $this->args["TYPE"] = "UPDATE";
                $this->args["UPDATE_ARGS"]  = $updateArguments;
                $updateQuery = $this->buildQuery();
                $bindArray = array_merge(array_values($this->args["UPDATE_ARGS"]), array_values($this->args["BIND_VALUES"]));
                return (bool) $this->executeQuery($updateQuery, $bindArray);
            }


            /**
             * @param string $row
             * @param int    $increment
             */
            public function increment(string $row, int $increment = 1){
                $this->_dbh->query("UPDATE {$this->args["TABLE"]} SET {$row} = {$row} + $increment");
            }

            /**
             * @param string $row
             * @param int    $decrement
             */
            public function decrement(string $row, int $decrement = 1){
                $this->_dbh->query("UPDATE {$this->args["TABLE"]} SET {$row} = {$row} - $decrement");
            }


            /**
             * @param $table
             * @param $column1
             * @param $operator
             * @param $column2
             *
             * @return $this
             *
             */
            public function join($table, $column1, $operator, $column2){
                $this->args["JOIN"][] = array(
                    "TYPE"      => "INNER JOIN",
                    "TABLE"     => $table,
                    "WHERE"     => array(
                        "column1"   => $column1,
                        "operator"  => $operator,
                        "column2"   => $column2
                    )
                );

                return $this;
            }

            /**
             * @return array
             *
             */
            public function get(){
//                if ($this->executeQuery($this->buildQuery(), array_values($this->args["BIND_VALUES"]))){
//                    return $queryExec->fetchAll();
//                }
//                die($this->buildQuery());
                $result = $this->_dbh->prepare($this->buildQuery());
                $result->execute($this->args["BIND_VALUES"]);
                return $result->fetchAll(\PDO::FETCH_ASSOC);

            }

        }
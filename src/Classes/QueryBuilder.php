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
            private static $unionArray = [];
            private $args = array(
                "TABLE"             =>       [],
                "TYPE"              =>       [],
                "COLUMNS"           =>       [],
                "WHERE"             =>       [],
                "ORDERBY"           =>       [],
                "GROUP_BY"           =>      [],
                "LIMIT"             =>       [],
                "DISTINCT"          =>       false,
                "FROM"              =>       [],
                "INSERT"            =>       [],
                "JOIN"              =>       [],
                "UPDATE_ARGS"       =>       [],
                "BIND_VALUES"       =>       [],
                "RAW_STMT"          =>       [],
                "TEST_ROWS_COUNT"   =>       50
            );

            private static $registeredEvents = array(
                "before" => [],
                "after"  => []
            );
            private static $rawParam = "";
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
                                call_user_func(array($this, 'whereBuilder'), $logical, array_keys($whereArguments[0])[0], array_values($whereArguments[0])[0]);
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
                    if (is_array($argSetValue["value"])){
                        $valueArray = $argSetValue["value"];
                        $placeholder = implode(",", array_fill(0,count($valueArray),"?"));
                        $whereStr .= "`".$argSetValue["column"] ."` ". $argSetValue["operator"]." (".$placeholder.")";
                        foreach ($valueArray as $val){
                            $this->args["BIND_VALUES"][] = $val;
                        }
                    }elseif(is_null($argSetValue["value"])){
                        $whereStr .= $argSetValue["column"] ." ". $argSetValue["operator"]." NULL ";
                    }else{
                        $whereStr .= $argSetValue["column"] ." ". $argSetValue["operator"]." ". "?";
                        $this->args["BIND_VALUES"][] = $argSetValue['value'];
                    }
                    //Loading bind array in same order placeholders where created
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
            private function loadWhere($logical, array $whereArguments){
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


            private function getJoins(){
                $joinCompoundArray = $this->args["JOIN"];
                $formedSql = '';
                foreach ($joinCompoundArray as $joinArrayIndex => $joinArray){
                    $formedSql .= $joinArray["TYPE"]." ".$joinArray["TABLE"]." ON ";
                    $joinArray = $joinArray["WHERE"];
                    $formedSql .= "`{$joinArray["column1"]}`"." ". $joinArray["operator"]." ".$joinArray["column2"]." ";
                }
                return $formedSql;
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




            private function buildQuery(){
                try {
                    //Loading default values for required query parts


                    $sql = '';

                    $this->arrayLoadDefault("SELECT", $this->args["TYPE"]);
                    $this->arrayLoadDefault([["column" => "*"]], $this->args["COLUMNS"]);

                    $type = $this->args["TYPE"];
                    $table = "`{$this->args["TABLE"]}`";
                    $limit = implode(",", $this->args["LIMIT"]);
                    $joins = $this->getJoins();
                    $conditionals = $this->getConditionals();

                    $columnArray = $this->args["COLUMNS"];

                    if (isset($columnArray) && !empty($columnArray)){
                        if($this->args["COLUMNS"][0]["column"] !== "*"){
                            $columns = implode("`, `",array_values(array_column($this->args["COLUMNS"], "column")));
                            $columns = str_pad($columns, strlen($columns)+2, "`", STR_PAD_BOTH);
                        }elseif(empty(self::$rawParam)){
                            $columns = "*";
                        }else{
                            $columns = self::$rawParam;
                        }
                    }

                    $columns = $columns??"";
                    $limit = $limit ? " LIMIT ". $limit : "";
                    $conditionals = $conditionals ? " WHERE ".$conditionals : "";
                    $group = $this->args["GROUP_BY"]? " GROUP BY ".$this->args["GROUP_BY"]." ":"";
                    $order = $this->args["ORDERBY"]? " ORDER BY {$this->args["ORDERBY"]["column"]} {$this->args["ORDERBY"]["order"]} ":"";

                    switch(explode(" ",$this->args["TYPE"])[0]){
                        case "SELECT":
                        case "DELETE":
                            $sql = $type." ".$columns." FROM ".$table.$conditionals.$group.$order.$joins.$limit;
                            break;
                        case "UPDATE":
                            $updatesArr = [];
                            foreach ($this->args["UPDATE_ARGS"] as $tblColumn => $newValue){
                                $updatesArr[] = $tblColumn . " = ". "?";
                            }
                            $updates = implode(",", $updatesArr);
                            $sql = $type." ".$table." SET ".$updates." WHERE ".$conditionals;

                            break;
                        case "INSERT":
                            // Placeholders as ?
                            $valuesPlaceholder = implode(",",array_fill(0, count($this->args["COLUMNS"]), "?"));
                            $sql = "INSERT INTO $table ($columns) VALUES($valuesPlaceholder)";
                            break;
                        default:
                            die("Call my creator, something unusual happened");
                    }
                    return $sql;
                }catch(\Exception $e){
                    die($e->getMessage());
                }
            }

            public static function raw($param){
                self::$rawParam = $param;
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
                    $registeredEventsBefore = self::$registeredEvents["before"];
                    foreach ($registeredEventsBefore as $bKey => $bevents){
                        $bType = explode("-", $registeredEventsBefore[$bKey]["type"])[1];
                        if ($bType === strtolower($this->args["TYPE"]) && $registeredEventsBefore[$bKey]["table"] === $this->args["TABLE"]){
                            $registeredEventsBefore[$bKey]["callable"](1);
                        }
                    }
                    $stmt = $this->_dbh->prepare($query);
                    $stmt->execute($bindParams);
                    $registeredEventsAfter = self::$registeredEvents["after"];
                    foreach ($registeredEventsAfter as $aKey => $aevents){
                        $aType = explode("-", $registeredEventsAfter[$aKey]["type"])[1];
                        if ($aType === strtolower($this->args["TYPE"]) && $registeredEventsAfter[$aKey]["table"] === $this->args["TABLE"]){
                            $lastInsertid = null;
                            if ($aType === "insert"){
                                $lastInsertid = $this->_dbh->lastInsertId();
                            }
                            $registeredEventsAfter[$aKey]["callable"]($lastInsertid);
                        }
                    }

                    return $stmt;

                }catch(\PDOException $e){
                    die($e->getMessage());
                }
            }


            private function rangedWhereBuilder($column, array $whereRange, $operator = "IN"){
                $val = [$column, $operator, $whereRange];
                call_user_func(array($this, 'loadWhere'), "AND", $val);
                return $this;
            }


            private function nullWhere($column, $operator = "IS"){
                $val = [$column, $operator, null];
                call_user_func(array($this, 'loadWhere'), "AND", $val);
            }

            private function buildJoin($table, $column1, $operator, $column2, $type = "JOIN"){
                $this->args["JOIN"][] = array(
                    "TYPE"      => $type,
                    "TABLE"     => $table,
                    "WHERE"     => array(
                        "column1"   => $column1,
                        "operator"  => $operator,
                        "column2"   => $column2
                    )
                );
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
                $this->args["TYPE"] = "INSERT";
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
                $values = array_values(array_column($this->args["COLUMNS"], "value"));
                return boolval($this->executeQuery($this->buildQuery(), $values));
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
             * @return $this
             */
            public function distinct(){
                $this->args["TYPE"] = "SELECT DISTINCT";
                return $this;
            }

            /**
             * @return mixed
             *
             */
            public function count(){
                $this->args["TYPE"] = "SELECT COUNT(*)";
                unset($this->args["COLUMNS"]);
                return $this->executeQuery($this->buildQuery())->fetchColumn();
            }

            /**
             * @param mixed ...$whereArguments
             * For WHERE clauses with WHERE condition
             * @return $this
             */
            public function orWhere(...$whereArguments){
                array_unshift($whereArguments, "OR");
                call_user_func_array(array($this, 'whereBuilder'), $whereArguments);
//                var_dump($this->args["WHERE"]);

                return $this;
            }


            public function isNull($column){
                 $this->nullWhere($column);
                 return $this;
            }

            public function isNotNull($column){
                $this->nullWhere($column, "IS NOT");
                return $this;
            }

            public function whereIn($column, array $whereRange){
                $this->rangedWhereBuilder($column, $whereRange);
                return $this;
            }

            public function whereNotIn($column, array $whereRange){
                $this->rangedWhereBuilder($column, $whereRange, "NOT IN");
                return $this;
            }

            public function whereBetween($column, array $whereRange){
                $this->rangedWhereBuilder($column, range($whereRange[0], $whereRange[1]));
                return $this;
            }

            public function whereNotBetween($column, array $whereRange){
                $this->rangedWhereBuilder($column, range($whereRange[0], $whereRange[1]), "NOT IN");
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
//                var_dump(array_values($this->args["BIND_VALUES"]));
                return (bool) $this->executeQuery($this->buildQuery());
            }


            /**
             * @param $column
             *
             * @return $this
             *
             */
            public function groupBy($column){
                $this->args["GROUP_BY"] = $column;
                return $this;
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



            public function join($table, $column1, $operator, $column2){

                $this->buildJoin($table, $column1, $operator, $column2);
                return $this;
            }

            public function leftJoin($table, $column1, $operator, $column2){
                $this->buildJoin($table, $column1, $operator, $column2, "LEFT JOIN");
                return $this;
            }

            public function rightJoin($table, $column1, $operator, $column2){
                $this->buildJoin($table, $column1, $operator, $column2, "RIGHT JOIN");
                return $this;
            }

            public static function registerEvent(string $eventType, string $table, callable $callback){
                try{
                    $typeStr = explode("-", $eventType);
                    if (!in_array($typeStr[1], ["select", "update", "delete", "insert"])){
                        throw new InvalidArgumentException("Invalid event passed to registerEvent() method");
                    }
                    switch ($typeStr[0]){
                        case "before":
                        case "after":
                            self::$registeredEvents[$typeStr[0]][] = array(
                                "type"          => $eventType,
                                "table"         => $table,
                                "callable"      => $callback
                            );
                            break;
                        default:
                            throw new InvalidArgumentException("Invalid event passed to registerEvent() method");

                    }

                    return true;
                }catch(InvalidArgumentException $e){
                    die($e->getMessage());
                }
            }

            public function getRegisteredEvents(){
                return self::$registeredEvents;
            }
            public function getSql(){
                return $this->buildQuery();
            }
            /**
             * @return array
             *
             */

            public function get(){
                //Check Before Event

                $query = $this->buildQuery();
                $result = $this->executeQuery($query);
                return $result->fetchAll(\PDO::FETCH_ASSOC);

            }

        }
<?php

        /**
         * Created by PhpStorm.
         * User: GARUBA
         * Date: 2/5/2019
         * Time: 6:39 PM
         */

        namespace App\Classes;
        use App\Classes\Database;
        use App\Exceptions\InvalidArgumentsCountException;

        class QueryBuilder {

            private $_dbh;
            private $args;


            /**
             * QueryBuilder constructor.
             *
             */
            public function __construct(){
                // Initialize $_dbh to hold an instance of the PDO object 
                $this->_dbh = Database::getInstance()->getConnection();

                $this->args = array(
                    "TYPE"          =>      "SELECT",
                    "COLUMNS"       =>      "*",
                    "WHERE"         =>       [],
                    "ORDERBY"       =>       [],
                    "LOGICAL"       =>       [],
                    "LIMIT"         =>       []
                );
            }

            /**
             * @param \App\Classes\string $table
             * Sets the name of table or tables where SQL operations will work on
             * @return $this
             */
            public function table(string $table){
                $this->args['TABLE'] = $table;
                return $this;
            }

            /**
             * @param null|string $logical
             * @param mixed       ...$whereArguments
             *
             *  Sets the SQL WHERE clause arguments and also stores the logical conditions attached to each in an array
             *  Default is AND
             * @return $this
             */
            public function where(?string $logical, ...$whereArguments){
                $logical = $logical ?: "AND";
                try {
                    switch(count($whereArguments)){
                        case 3:
                            $this->args["WHERE"][] = $whereArguments;
                            $this->args["LOGICAL"][] = $logical;

                            break;
                        case 2:
                            array_splice($whereArguments, 1, 0, "=");
                            $this->args["WHERE"][] = $whereArguments;
                            $this->args["LOGICAL"][] = $logical;
                            break;
                        case 1:
                            if ($this->containsArray($whereArguments[0])){
                                foreach ($whereArguments[0] as $arg){
                                    array_unshift($arg, $logical);
                                    call_user_func_array(array($this, 'where'), $arg);
                                }
                            }else{
                                array_unshift($whereArguments, $logical);
                                call_user_func_array(array($this, 'where'), $whereArguments);
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

            /**
             * @param mixed ...$whereArguments
             * For WHERE clauses with WHERE condition
             * @return $this
             */
            public function orWhere(...$whereArguments){
                array_unshift($whereArguments, "OR");
                call_user_func_array(array($this, 'where'), $whereArguments);

                return $this;
            }


            public function first(){
                $this->args["LIMIT"] = 1;
                // Suppose to also execute query
            }

            public function pluck(string $columns){
                $this->args["COLUMNS"] = $columns;
                return $this;
            }
            public function containsArray(array $array): bool{
                foreach ($array as $arr){
                    if (is_array($arr)){
                        return true;
                    }
                }
                return false;
            }

            public function get(){
                $queryTemplate  = "{$this->args['TYPE']} {$this->args['COLUMNS']} FROM {$this->args['TABLE']}";
                if (!empty($this->args['WHERE'])){
                    $queryTemplate .= " WHERE ";
                    foreach ($this->args['WHERE'] as $clause){
                        $queryTemplate .= implode(" ", $clause);
                    }
                    print_r($this->args['LOGICAL']);
                }

                return $queryTemplate;
            }
        }
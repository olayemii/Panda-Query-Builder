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



            public function __construct(){
                // Initialize $_dbh to hold an instance of the PDO object 
                $this->_dbh = Database::getInstance()->getConnection();

                $this->args = array(
                    "TYPE"          =>      "SELECT",
                    "COLUMNS"       =>      "*",
                    "WHERE"         =>       [],
                    "ORDERBY"       =>       [],
                    "LOGICAL"       =>       []
                );
            }

            /**
             * @param \App\Classes\string $table
             *
             * @return $this
             */
            public function table(string $table){
                $this->args['TABLE'] = $table;
                return $this;
            }

            public function where(){
                $funcArguments = func_get_args();
                try {
                    switch(count($funcArguments)){
                        case 3:
                            $this->args["WHERE"][] = $funcArguments;
                            break;
                        case 2:
                            array_splice($funcArguments, 1, 0, "=");
                            $this->args["WHERE"][] = $funcArguments;
                            break;
                        case 1:
                            if ($this->containsArray(func_get_arg(0))){
                                foreach (func_get_arg(0) as $arg){
                                    call_user_func_array(array($this, 'where'), $arg);
                                }

                            }else{
                                call_user_func_array(array($this, 'where'), func_get_arg(0));
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
                    print_r($this->args['WHERE']);
                }

                return $queryTemplate;
            }
        }
<?php
/**
 * Created by PhpStorm.
 * User: GARUBA
 * Date: 2/5/2019
 * Time: 6:39 PM
 */

namespace App\Exceptions;


use Throwable;

class InvalidArgumentsCountException extends \Exception
{
    public function __construct($message = "", $code = 0,
        \Throwable $previous = null
    ) {
        $message ?: "Incorrect amount of arguments passed to method";
        parent::__construct($message, $code, $previous);
    }
}
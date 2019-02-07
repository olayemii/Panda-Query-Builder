<?php
/**
 * Created by PhpStorm.
 * User: GARUBA
 * Date: 2/5/2019
 * Time: 7:42 PM
 */

namespace App\Exceptions;


use Throwable;

class InvalidArgumentException extends \Exception
{
    public function __construct(string $message = "", int $code = 0,
        \Throwable $previous = null
    ) {
        $message = $message ?? "Invalid Argument passed to method";
        parent::__construct($message, $code, $previous);
    }
}
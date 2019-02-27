<?php
/**
 * Created by PhpStorm.
 * User: GARUBA
 * Date: 2/27/2019
 * Time: 1:25 PM
 */

namespace App\Exceptions;


use Throwable;

class PropertyNotRegisteredException extends \Exception
{
    public function __construct(string $message = "", int $code = 0,
        \Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
<?php
namespace Sentegrity\BusinessBundle\Exceptions;

use \Exception;

class QueryFailedException extends Exception
{
    public function __construct($message = "Internal Server Error", $code = 500, Exception $previous = null)
    {
        // make sure everything is assigned properly
        parent::__construct($message, $code, $previous);
    }


    public function customFunction()
    {
        echo "A custom function for this type of exception\n";
    }
} 
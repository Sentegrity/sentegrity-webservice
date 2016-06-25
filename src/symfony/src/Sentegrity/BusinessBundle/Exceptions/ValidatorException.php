<?php
namespace Sentegrity\BusinessBundle\Exceptions;


use \Exception;

class ValidatorException extends Exception
{
    public $object;

    public function __construct($object, $message = "Invalid fields", $code = 0, Exception $previous = null)
    {
        // make sure everything is assigned properly
        parent::__construct($message, $code, $previous);
        $this->object = $object;
    }


    public function customFunction()
    {
        echo "A custom function for this type of exception\n";
    }

    public function getBody()
    {
        return $this->object;
    }
}
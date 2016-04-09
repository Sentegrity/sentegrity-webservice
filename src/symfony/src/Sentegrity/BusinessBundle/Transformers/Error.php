<?php
namespace Sentegrity\BusinessBundle\Transformers;

class Error
{
    public $code;
    public $message;
    public $developer;

    function __construct($code, $msg, $dMsg)
    {
        $this->code = $code;
        $this->message = $msg;
        $this->developer = $dMsg;
    }
} 
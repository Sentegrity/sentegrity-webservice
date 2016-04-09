<?php
namespace Sentegrity\BusinessBundle\Transformers;

use Symfony\Component\HttpFoundation\Response as HttpResponse;

class Response
{
    public $system;
    public $data;

    function __construct($mData, $err, $data)
    {
        $this->system = new \stdClass();
        $this->system->metadata = $mData;
        $this->system->error = $err;
        $this->data = $data;
    }
} 
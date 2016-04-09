<?php
namespace Sentegrity\BusinessBundle\Transformers;

class Metadata
{
    public $apiVersion;

    function __construct($ver, $at)
    {
        $this->apiVersion = $ver;
    }
} 
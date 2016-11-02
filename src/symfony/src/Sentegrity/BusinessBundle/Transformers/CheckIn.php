<?php
namespace Sentegrity\BusinessBundle\Transformers;


class CheckIn implements \JsonSerializable
{
    private $policy = null;
    private $policyExists = true;

    /**
     * @param mixed $policy
     */
    public function setPolicy($policy)
    {
        $this->policy = $policy;
    }

    /**
     * @param mixed $policyExists
     */
    public function setPolicyExists($policyExists)
    {
        $this->policyExists = $policyExists;
    }

    function jsonSerialize()
    {
        return [
            'newPolicy'        => $this->policy,
            'policyExists'  => $this->policyExists
        ];
    }
}
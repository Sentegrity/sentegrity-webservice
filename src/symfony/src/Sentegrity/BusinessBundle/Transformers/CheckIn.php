<?php
namespace Sentegrity\BusinessBundle\Transformers;


class CheckIn implements \JsonSerializable
{
    private $policy = null;
    private $policyOrganizationExists = true;

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
    public function setPolicyExists($policyOrganizationExists)
    {
        $this->policyOrganizationExists = $policyOrganizationExists;
    }

    function jsonSerialize()
    {
        return [
            'newPolicy'                 => $this->policy,
            'policyOrganizationExists'  => $this->policyOrganizationExists
        ];
    }
}
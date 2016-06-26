<?php
namespace Sentegrity\BusinessBundle\Transformers;

use Sentegrity\BusinessBundle\Entity\Documents\Organization as OrganizationEntity;
use Sentegrity\BusinessBundle\Entity\Documents\Groups as GroupEntity;

class Organization implements \JsonSerializable
{
    private $uuid;
    private $name;
    private $domainName;
    private $contact;
    private $defaultPolicies;

    function __construct(
        OrganizationEntity $organization,
        GroupEntity $group
    )
    {
        $this->uuid = $organization->getUuid();
        $this->name = $organization->getName();
        $this->domainName = $organization->getDomainName();

        $this->contact = new \stdClass();
        $this->contact->name = $organization->getContactName();
        $this->contact->email = $organization->getContactEmail();
        $this->contact->phone = $organization->getContactPhone();
        
        $this->defaultPolicies = new \stdClass();
        $this->defaultPolicies->ios = $group->getPolicyIos()->getUuid();
        $this->defaultPolicies->android = $group->getPolicyAndroid()->getUuid();
    }

    function jsonSerialize()
    {
        return [
            'uuid'              => $this->uuid,
            'name'              => $this->name,
            'domainName'        => $this->domainName,
            'contact'           => $this->contact,
            'defaultPolicies'   => $this->defaultPolicies
        ];
    }
}
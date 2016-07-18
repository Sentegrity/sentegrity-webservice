<?php
namespace Sentegrity\BusinessBundle\Transformers;

use Sentegrity\BusinessBundle\Entity\Documents\Policy as PolicyEntity;

class Policy implements \JsonSerializable
{
    private $uuid;
    private $name;
    private $platform;
    private $isDefault;
    private $appVersion;
    private $revision;
    private $data;

    function __construct(PolicyEntity $policy)
    {
        $this->uuid = $policy->getUuid();
        $this->name = $policy->getName();
        $this->platform = $policy->getPlatform();
        $this->isDefault = (bool)$policy->getIsDefault();
        $this->appVersion = $policy->getAppVersion();
        $this->revision = (int)$policy->getRevisionNo();
        $this->data = json_decode($policy->getData(), true);
    }

    function jsonSerialize()
    {
        return [
            'uuid'          => $this->uuid,
            'name'          => $this->name,
            'platform'      => $this->platform,
            'isDefault'     => $this->isDefault,
            'appVersion'    => $this->appVersion,
            'revision'      => $this->revision,
            'data'          => $this->data
        ];
    }
}
<?php
namespace Sentegrity\BusinessBundle\Transformers;


class TopRisks extends DashboardTopData implements \JsonSerializable
{
    public $userActivationId;
    public $deviceSalt;
    public $phoneModel;
    public $trustScore;

    function __construct($item)
    {
        $this->userActivationId = $item['user_activation_id'];
        $this->deviceSalt = $item['device_salt'];
        $this->phoneModel = $item['phone_model'];
        $this->trustScore = (float)$item['trust_score'];
    }

    function jsonSerialize()
    {
        return [
            'userActivationId'  => $this->userActivationId,
            'deviceSalt'        => $this->deviceSalt,
            'phoneModel'        => $this->phoneModel,
            'trustScore'        => $this->trustScore
        ];
    }
}
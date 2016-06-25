<?php

namespace Sentegrity\BusinessBundle\Entity\Documents;

use Doctrine\ORM\Mapping as ORM;

/**
 * 24HourRunHistory
 *
 * @ORM\Table(
 *     name="24_hour_run_history"
 * )
 * @ORM\Entity(
 *     repositoryClass="Sentegrity\BusinessBundle\Entity\Repository\HourRunHistoryRepository"
 * )
 */
class HourRunHistory
{
    /**
     * @var int
     *
     * @ORM\Column(
     *     name="id", 
     *     type="integer"
     * )
     * @ORM\Id
     * @ORM\GeneratedValue(
     *     strategy="AUTO"
     * )
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(
     *     name="device_salt", 
     *     type="string", 
     *     length=255,
     *     nullable=false,
     *     unique=true
     * )
     */
    private $deviceSalt;

    /**
     * @var int
     *
     * @ORM\Column(
     *     name="upload_timestamp", 
     *     type="integer"
     * )
     */
    private $uploadTimestamp;

    /**
     * @var string
     *
     * @ORM\Column(
     *     name="phone_model", 
     *     type="string", 
     *     length=255
     * )
     */
    private $phoneModel;

    /**
     * @var int
     *
     * @ORM\Column(
     *     name="platform", 
     *     type="smallint",
     *     length=2,
     *     nullable=false
     * )
     */
    private $platform;

    /**
     * @var string
     *
     * @ORM\Column(
     *     name="json",
     *     type="text"
     * )
     */
    private $json;

    /**
     * @var int
     *
     *  @ORM\Column(
     *     name="user_activation_id",
     *     type="string",
     *     nullable=false,
     *     unique=true,
     *     options={
     *         "comment":"FK from user table"
     *     }
     * )
     */
    private $userActivationId;

    /**
     * @var int
     *
     * @ORM\Column(
     *     name="organization_id", 
     *     type="integer",
     *     nullable=false,
     *     options={
     *         "comment":"FK from organization table. If 0 default organization owns the policy."
     *     }
     * )
     */
    private $organizationId;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set deviceSalt
     *
     * @param string $deviceSalt
     *
     * @return 24HourRunHistory
     */
    public function setDeviceSalt($deviceSalt)
    {
        $this->deviceSalt = $deviceSalt;

        return $this;
    }

    /**
     * Get deviceSalt
     *
     * @return string
     */
    public function getDeviceSalt()
    {
        return $this->deviceSalt;
    }

    /**
     * Set uploadTimestamp
     *
     * @param integer $uploadTimestamp
     *
     * @return 24HourRunHistory
     */
    public function setUploadTimestamp($uploadTimestamp)
    {
        $this->uploadTimestamp = $uploadTimestamp;

        return $this;
    }

    /**
     * Get uploadTimestamp
     *
     * @return int
     */
    public function getUploadTimestamp()
    {
        return $this->uploadTimestamp;
    }

    /**
     * Set phoneModel
     *
     * @param string $phoneModel
     *
     * @return 24HourRunHistory
     */
    public function setPhoneModel($phoneModel)
    {
        $this->phoneModel = $phoneModel;

        return $this;
    }

    /**
     * Get phoneModel
     *
     * @return string
     */
    public function getPhoneModel()
    {
        return $this->phoneModel;
    }

    /**
     * Set platform
     *
     * @param integer $platform
     *
     * @return 24HourRunHistory
     */
    public function setPlatform($platform)
    {
        $this->platform = $platform;

        return $this;
    }

    /**
     * Get platform
     *
     * @return int
     */
    public function getPlatform()
    {
        return $this->platform;
    }

    /**
     * Set json
     *
     * @param string $json
     *
     * @return 24HourRunHistory
     */
    public function setJson($json)
    {
        $this->json = $json;

        return $this;
    }

    /**
     * Get json
     *
     * @return string
     */
    public function getJson()
    {
        return $this->json;
    }

    /**
     * Set userActivationId
     *
     * @param integer $userActivationId
     *
     * @return 24HourRunHistory
     */
    public function setUserActivationId($userActivationId)
    {
        $this->userActivationId = $userActivationId;

        return $this;
    }

    /**
     * Get userActivationId
     *
     * @return int
     */
    public function getUserActivationId()
    {
        return $this->userActivationId;
    }

    /**
     * Set organizationId
     *
     * @param integer $organizationId
     *
     * @return 24HourRunHistory
     */
    public function setOrganizationId($organizationId)
    {
        $this->organizationId = $organizationId;

        return $this;
    }

    /**
     * Get organizationId
     *
     * @return int
     */
    public function getOrganizationId()
    {
        return $this->organizationId;
    }
}


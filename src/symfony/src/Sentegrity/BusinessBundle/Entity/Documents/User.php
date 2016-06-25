<?php

namespace Sentegrity\BusinessBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * User
 *
 * @ORM\Table(
 *     name="user",
 *     indexes={
 *          @ORM\Index(
 *              name="i_group_org",
 *              columns={
 *                  "group_id",
 *                  "organization_id"
 *              }
 *          )
 *      }
 * )
 * @ORM\Entity(
 *     repositoryClass="Sentegrity\BusinessBundle\Entity\Repository\UserRepository"
 * )
 */
class User
{
    /**
     * @var int
     *
     * @ORM\Column(
     *     name="device_user_id",
     *     type="string",
     *     options={
     *         "comment":"Uniquely generated if the “Device Activation ID” was not found in the table (a new record is created)"
     *     }
     * )
     * @ORM\Id
     */
    private $deviceUserId;

    /**
     * @var string
     *
     * @ORM\Column(
     *     name="device_activation_id",
     *     type="string",
     *     length=255,
     *     nullable=false,
     *     options={
     *         "comment":"The user full email sent during the first policy update request"
     *     }
     * )
     */
    private $deviceActivationId;

    /**
     * @var int
     *
     * @ORM\Column(
     *     name="organization_id",
     *     type="integer",
     *     nullable=false,
     *     options={
     *         "comment":"FK from organization table."
     *     }
     * )
     */
    private $organizationId;

    /**
     * @var int
     *
     * @ORM\Column(
     *     name="group_id",
     *     type="integer",
     *     nullable=false,
     *     options={
     *         "comment":"FK from organization table."
     *     }
     * )
     */
    private $groupId;


    /**
     * Get deviceUserId
     *
     * @return int
     */
    public function getDeviceUserId()
    {
        return $this->deviceUserId;
    }

    /**
     * Set deviceActivationId
     *
     * @param string $deviceActivationId
     *
     * @return User
     */
    public function setDeviceActivationId($deviceActivationId)
    {
        $this->deviceActivationId = $deviceActivationId;

        return $this;
    }

    /**
     * Get deviceActivationId
     *
     * @return string
     */
    public function getDeviceActivationId()
    {
        return $this->deviceActivationId;
    }

    /**
     * Set organizationId
     *
     * @param integer $organizationId
     *
     * @return User
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

    /**
     * Set groupId
     *
     * @param integer $groupId
     *
     * @return User
     */
    public function setGroupId($groupId)
    {
        $this->groupId = $groupId;

        return $this;
    }

    /**
     * Get groupId
     *
     * @return int
     */
    public function getGroupId()
    {
        return $this->groupId;
    }
}


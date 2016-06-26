<?php

namespace Sentegrity\BusinessBundle\Entity\Documents;

use Doctrine\ORM\Mapping as ORM;

/**
 * Groups
 *
 * @ORM\Table(
 *     name="groups",
 *     options={
 *          "comment":"When a new organization is created a new record is created in the
 *                     Organization table and a new record is created in the Group table for an ID of 0
 *                     which is linked default iOS/Android policies."
 *     },
 *     indexes={
 *          @ORM\Index(
 *              name="i_id",
 *              columns={
 *                  "group_id"
 *              }
 *          ),
 *          @ORM\Index(
 *              name="i_id_pol_ios_org",
 *              columns={
 *                  "group_id",
 *                  "policy_id_ios",
 *                  "organization_id"
 *              }
 *          ),
 *          @ORM\Index(
 *              name="i_id_pol_android_org",
 *              columns={
 *                  "group_id",
 *                  "policy_id_android",
 *                  "organization_id"
 *              }
 *          )
 *      }
 * )
 * @ORM\Entity(
 *     repositoryClass="Sentegrity\BusinessBundle\Entity\Repository\GroupsRepository"
 * )
 */
class Groups
{
    /**
     * @var int
     *
     * @ORM\Column(
     *     name="group_id",
     *     type="integer"
     * )
     * @ORM\Id
     */
    private $groupId;

    /**
     * @var string
     *
     * @ORM\Column(
     *     name="name",
     *     type="string",
     *     length=255,
     *     nullable=false,
     *     options={
     *         "default": "Default Group",
     *         "collation":"utf8_unicode_ci"
     *     }
     * )
     */
    private $name = 'Default Group';

    /**
     * @var int
     *
     * @ORM\Column(
     *     name="policy_id_ios",
     *     type="integer",
     *     nullable=false,
     *     options={
     *         "comment":"FK from policy table for iOS."
     *     }
     * )
     */
    private $policyIdIos;

    /**
     * @var int
     *
     * @ORM\Column(
     *     name="policy_id_android",
     *     type="integer",
     *     nullable=false,
     *     options={
     *         "comment":"FK from policy table for Android."
     *     }
     * )
     */
    private $policyIdAndroid;

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
     * @ORM\Id
     */
    private $organizationId;


    public function __construct($groupId, $organizationId)
    {
        $this->groupId = $groupId;
        $this->organizationId = $organizationId;
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

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Groups
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set policyIdIos
     *
     * @param integer $policyIdIos
     *
     * @return Groups
     */
    public function setPolicyIdIos($policyIdIos)
    {
        $this->policyIdIos = $policyIdIos;

        return $this;
    }

    /**
     * Get policyIdIos
     *
     * @return int
     */
    public function getPolicyIdIos()
    {
        return $this->policyIdIos;
    }

    /**
     * Set policyIdAndroid
     *
     * @param integer $policyIdAndroid
     *
     * @return Groups
     */
    public function setPolicyIdAndroid($policyIdAndroid)
    {
        $this->policyIdAndroid = $policyIdAndroid;

        return $this;
    }

    /**
     * Get policyIdAndroid
     *
     * @return int
     */
    public function getPolicyIdAndroid()
    {
        return $this->policyIdAndroid;
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


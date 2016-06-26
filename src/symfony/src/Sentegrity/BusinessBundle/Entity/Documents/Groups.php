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
     * @ORM\ManyToOne(
     *     targetEntity="Policy",
     *     cascade={"persist"}
     * )
     * @ORM\JoinColumn(
     *     name="policy_id_ios",
     *     referencedColumnName="id"
     * )
     */
    private $policyIos;

    /**
     * @var int
     *
     * @ORM\ManyToOne(
     *     targetEntity="Policy",
     *     cascade={"persist"}
     * )
     * @ORM\JoinColumn(
     *     name="policy_id_android",
     *     referencedColumnName="id"
     * )
     */
    private $policyAndroid;

    /**
     * @var int
     *
     * @ORM\ManyToOne(
     *     targetEntity="Organization",
     *     cascade={"persist"}
     * )
     * @ORM\JoinColumn(
     *     name="organization_id",
     *     referencedColumnName="id",
     *     onDelete="CASCADE"
     * )
     * @ORM\Id
     */
    private $organization;


    public function __construct($groupId, Organization $organization)
    {
        $this->groupId = $groupId;
        $this->organization = $organization;
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
     * Set policyIos
     *
     * @param Policy $policyIos
     *
     * @return Groups
     */
    public function setPolicyIos(Policy $policyIos)
    {
        $this->policyIos = $policyIos;

        return $this;
    }

    /**
     * Get policyIos
     *
     * @return Policy
     */
    public function getPolicyIos()
    {
        return $this->policyIos;
    }

    /**
     * Set policyAndroid
     *
     * @param Policy $policyAndroid
     *
     * @return Groups
     */
    public function setPolicyIdAndroid(Policy $policyAndroid)
    {
        $this->policyAndroid = $policyAndroid;

        return $this;
    }

    /**
     * Get policyAndroid
     *
     * @return Policy
     */
    public function getPolicyAndroid()
    {
        return $this->policyAndroid;
    }

    /**
     * Get organization
     *
     * @return Organization
     */
    public function getOrganization()
    {
        return $this->organization;
    }
}


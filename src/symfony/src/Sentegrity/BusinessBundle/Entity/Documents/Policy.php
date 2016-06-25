<?php

namespace Sentegrity\BusinessBundle\Entity\Documents;

use Doctrine\ORM\Mapping as ORM;

/**
 * Policy
 *
 * @ORM\Table(
 *     name="policy",
 *     indexes={
 *          @ORM\Index(
 *              name="i_platform",
 *              columns={
 *                  "platform",
 *                  "organization_owner_id"
 *              }
 *          )
 *      }
 * )
 * @ORM\Entity(
 *     repositoryClass="Sentegrity\BusinessBundle\Entity\Repository\PolicyRepository"
 * )
 */
class Policy
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
     *     name="uuid",
     *     type="string",
     *     length=60,
     *     nullable=false
     * )
     */
    private $uuid;

    /**
     * @var string
     *
     * @ORM\Column(
     *     name="name", 
     *     type="string", 
     *     length=255,
     *     nullable=false,
     *     options={
     *         "collation":"utf8_unicode_ci"
     *     }
     * )
     */
    private $name;

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
     * @var bool
     *
     * @ORM\Column(
     *     name="is_default", 
     *     type="boolean",
     *     nullable=false,
     *     options={
     *         "default":"0"
     *     }
     * )
     */
    private $isDefault;

    /**
     * @var string
     *
     * @ORM\Column(
     *     name="app_version", 
     *     type="decimal", 
     *     precision=10, 
     *     scale=2,
     *     nullable=false
     * )
     */
    private $appVersion;

    /**
     * @var int
     *
     * @ORM\Column(
     *     name="revision_no", 
     *     type="integer",
     *     nullable=false
     * )
     */
    private $revisionNo;

    /**
     * @var string
     *
     * @ORM\Column(
     *     name="data", 
     *     type="text",
     *     nullable=false,
     *     options={
     *         "default":"1"
     *     }
     * )
     */
    private $data;

    /**
     * @var int
     *
     * @ORM\Column(
     *     name="organization_owner_id", 
     *     type="integer",
     *     nullable=false,
     *     options={
     *         "comment":"FK from organization table. If 0 default organization owns the policy."
     *     }
     * )
     */
    private $organizationOwnerId;


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
     * Set uuid
     *
     * @param string $uuid
     *
     * @return Policy
     */
    public function setUuid($uuid)
    {
        $this->uuid = $uuid;

        return $this;
    }

    /**
     * Get uuid
     *
     * @return string
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Policy
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
     * Set platform
     *
     * @param integer $platform
     *
     * @return Policy
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
     * Set isDefault
     *
     * @param boolean $isDefault
     *
     * @return Policy
     */
    public function setIsDefault($isDefault)
    {
        $this->isDefault = $isDefault;

        return $this;
    }

    /**
     * Get isDefault
     *
     * @return bool
     */
    public function getIsDefault()
    {
        return $this->isDefault;
    }

    /**
     * Set appVersion
     *
     * @param string $appVersion
     *
     * @return Policy
     */
    public function setAppVersion($appVersion)
    {
        $this->appVersion = $appVersion;

        return $this;
    }

    /**
     * Get appVersion
     *
     * @return string
     */
    public function getAppVersion()
    {
        return $this->appVersion;
    }

    /**
     * Set revisionNo
     *
     * @param integer $revisionNo
     *
     * @return Policy
     */
    public function setRevisionNo($revisionNo)
    {
        $this->revisionNo = $revisionNo;

        return $this;
    }

    /**
     * Get revisionNo
     *
     * @return int
     */
    public function getRevisionNo()
    {
        return $this->revisionNo;
    }

    /**
     * Set data
     *
     * @param string $data
     *
     * @return Policy
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Get data
     *
     * @return string
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Set organizationOwnerId
     *
     * @param integer $organizationOwnerId
     *
     * @return Policy
     */
    public function setOrganizationOwnerId($organizationOwnerId)
    {
        $this->organizationOwnerId = $organizationOwnerId;

        return $this;
    }

    /**
     * Get organizationOwnerId
     *
     * @return int
     */
    public function getOrganizationOwnerId()
    {
        return $this->organizationOwnerId;
    }
}


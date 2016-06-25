<?php

namespace Sentegrity\BusinessBundle\Entity\Documents;

use Doctrine\ORM\Mapping as ORM;

/**
 * Organization
 *
 * @ORM\Table(
 *     name="organization",
 *     indexes={
 *          @ORM\Index(
 *              name="i_domain",
 *              columns={
 *                  "domain_name"
 *              }
 *          ),
 *          @ORM\Index(
 *              name="i_uuid",
 *              columns={
 *                  "uuid"
 *              }
 *          )
 *      }
 * )
 * @ORM\Entity(
 *     repositoryClass="Sentegrity\BusinessBundle\Entity\Repository\OrganizationRepository"
 * )
 */
class Organization
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
     *     nullable=false
     * )
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(
     *     name="domain_name",
     *     type="string",
     *     length=100,
     *     nullable=false,
     *     options={
     *         "collation":"utf8_unicode_ci"
     *     }
     * )
     */
    private $domainName;

    /**
     * @var string
     *
     * @ORM\Column(
     *     name="contact_name",
     *     type="string",
     *     length=255,
     *     nullable=false,
     *     options={
     *         "collation":"utf8_unicode_ci"
     *     }
     * )
     */
    private $contactName;

    /**
     * @var string
     *
     * @ORM\Column(
     *     name="contact_email",
     *     type="string",
     *     length=255,
     *     nullable=false
     * )
     */
    private $contactEmail;

    /**
     * @var string
     *
     * @ORM\Column(
     *     name="contact_phone",
     *     type="string",
     *     length=20,
     *     nullable=false
     * )
     */
    private $contactPhone;


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
     * @return Organization
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
     * @return Organization
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
     * Set domainName
     *
     * @param string $domainName
     *
     * @return Organization
     */
    public function setDomainName($domainName)
    {
        $this->domainName = $domainName;

        return $this;
    }

    /**
     * Get domainName
     *
     * @return string
     */
    public function getDomainName()
    {
        return $this->domainName;
    }

    /**
     * Set contactName
     *
     * @param string $contactName
     *
     * @return Organization
     */
    public function setContactName($contactName)
    {
        $this->contactName = $contactName;

        return $this;
    }

    /**
     * Get contactName
     *
     * @return string
     */
    public function getContactName()
    {
        return $this->contactName;
    }

    /**
     * Set contactEmail
     *
     * @param string $contactEmail
     *
     * @return Organization
     */
    public function setContactEmail($contactEmail)
    {
        $this->contactEmail = $contactEmail;

        return $this;
    }

    /**
     * Get contactEmail
     *
     * @return string
     */
    public function getContactEmail()
    {
        return $this->contactEmail;
    }

    /**
     * Set contactPhone
     *
     * @param string $contactPhone
     *
     * @return Organization
     */
    public function setContactPhone($contactPhone)
    {
        $this->contactPhone = $contactPhone;

        return $this;
    }

    /**
     * Get contactPhone
     *
     * @return string
     */
    public function getContactPhone()
    {
        return $this->contactPhone;
    }
}


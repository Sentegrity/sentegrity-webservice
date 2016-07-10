<?php

namespace Sentegrity\BusinessBundle\Entity\Documents;

use Doctrine\ORM\Mapping as ORM;

/**
 * AdminSession
 *
 * @ORM\Table(
 *     name="admin_session"
 * )
 * @ORM\Entity(
 *     repositoryClass="Sentegrity\BusinessBundle\Entity\Repository\AdminSessionRepository"
 * )
 */
class AdminSession
{
    /**
     * @var int
     *
     * @ORM\Column(
     *     name="id", type="integer"
     * )
     * @ORM\Id
     * @ORM\GeneratedValue(
     *     strategy="AUTO"
     * )
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\ManyToOne(
     *     targetEntity="AdminUser",
     *     cascade={"persist"}
     * )
     * @ORM\JoinColumn(
     *     name="user_id",
     *     referencedColumnName="id",
     *     onDelete="CASCADE",
     *     nullable=true
     * )
     */
    private $user;

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
     *     onDelete="CASCADE",
     *     nullable=true
     * )
     */
    private $organization;

    /**
     * @var string
     *
     * @ORM\Column(
     *     name="permission",
     *     type="string",
     *     length=255
     * )
     */
    private $permission;

    /**
     * @var string
     *
     * @ORM\Column(
     *     name="access_token",
     *     type="string",
     *     length=255
     * )
     */
    private $accessToken;


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
     * Set user
     *
     * @param AdminUser $user
     *
     * @return AdminSession
     */
    public function setUser(AdminUser $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return AdminUser
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set organization
     *
     * @param Organization $organization
     *
     * @return AdminSession
     */
    public function setOrganization($organization)
    {
        $this->organization = $organization;

        return $this;
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

    /**
     * Set permission
     *
     * @param string $permission
     *
     * @return AdminSession
     */
    public function setPermission($permission)
    {
        $this->permission = $permission;

        return $this;
    }

    /**
     * Get permission
     *
     * @return string
     */
    public function getPermission()
    {
        return $this->permission;
    }

    /**
     * Set accessToken
     *
     * @param string $accessToken
     *
     * @return AdminSession
     */
    public function setAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;

        return $this;
    }

    /**
     * Get accessToken
     *
     * @return string
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }
}


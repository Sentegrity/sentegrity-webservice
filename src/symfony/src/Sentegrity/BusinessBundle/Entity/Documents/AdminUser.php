<?php

namespace Sentegrity\BusinessBundle\Entity\Documents;

use Doctrine\ORM\Mapping as ORM;

/**
 * AdminUser
 *
 * @ORM\Table(
 *     name="admin_user"
 * )
 * @ORM\Entity(
 *     repositoryClass="Sentegrity\BusinessBundle\Entity\Repository\AdminUserRepository"
 * )
 */
class AdminUser
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
     *     name="username",
     *     type="string",
     *     length=255
     * )
     */
    private $username;

    /**
     * @var string
     *
     * @ORM\Column(
     *     name="password",
     *     type="string",
     *     length=255
     * )
     */
    private $password;

    /**
     * @var int
     *
     * @ORM\Column(
     *     name="permission",
     *     type="integer"
     * )
     */
    private $permission;

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
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set username
     *
     * @param string $username
     *
     * @return AdminUser
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Get username
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set password
     *
     * @param string $password
     *
     * @return AdminUser
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set permission
     *
     * @param integer $permission
     *
     * @return AdminUser
     */
    public function setPermission($permission)
    {
        $this->permission = $permission;

        return $this;
    }

    /**
     * Get permission
     *
     * @return int
     */
    public function getPermission()
    {
        return $this->permission;
    }

    /**
     * Set organization
     *
     * @param Organization $organization
     *
     * @return AdminUser
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
}


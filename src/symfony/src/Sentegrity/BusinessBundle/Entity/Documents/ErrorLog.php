<?php

namespace Sentegrity\BusinessBundle\Entity\Documents;

use Doctrine\ORM\Mapping as ORM;

/**
 * ErrorLog
 *
 * @ORM\Table(
 *     name="error_log"
 * )
 * @ORM\Entity(
 *     repositoryClass="Sentegrity\BusinessBundle\Entity\Repository\ErrorLogRepository"
 * )
 */
class ErrorLog
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
     *     name="text",
     *     type="text"
     * )
     */
    private $text;

    /**
     * @var int
     *
     * @ORM\Column(
     *     name="type",
     *     type="integer"
     * )
     */
    private $type;

    /**
     * @var int
     *
     * @ORM\Column(
     *     name="created",
     *     type="integer"
     * )
     */
    private $created;


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
     * Set text
     *
     * @param string $text
     *
     * @return ErrorLog
     */
    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }

    /**
     * Get text
     *
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Set type
     *
     * @param integer $type
     *
     * @return ErrorLog
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set created
     *
     * @param integer $created
     *
     * @return ErrorLog
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return int
     */
    public function getCreated()
    {
        return $this->created;
    }
}


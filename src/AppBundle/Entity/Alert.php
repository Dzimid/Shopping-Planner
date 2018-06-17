<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class Alert
 * @package AppBundle\Entity
 *
 * @ORM\Entity()
 * @ORM\Table(name="alert")
 */
class Alert
{
    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=false)
     */
    private $content;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=false)
     */
    private $date;

    /**
     * @var User
     *
     * Many alerts one user
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User", inversedBy="alerts")
     * @ORM\JoinColumn(name="user", referencedColumnName="id")
     */
    private $user;

    /**
     * @var User
     *
     * Many alerts one user
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User")
     * @ORM\JoinColumn(name="sender", referencedColumnName="id")
     */
    private $sender;

    /**
     * Alert constructor.
     *
     * @param string    $content
     * @param \DateTime $date
     * @param User      $user
     * @param User      $from
     */
    public function __construct($content, \DateTime $date, User $user, User $from)
    {
        $this->content = $content;
        $this->date = $date;
        $this->user = $user;
        $this->sender = $from;
    }

    /******************************/

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param string $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param \DateTime $date
     */
    public function setDate($date)
    {
        $this->date = $date;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return User
     */
    public function getSender()
    {
        return $this->sender;
    }

    /**
     * @param User $sender
     */
    public function setSender($sender)
    {
        $this->sender = $sender;
    }
}
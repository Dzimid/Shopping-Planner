<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="place")
 */
class Place
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
     * @ORM\Column(type="string", length=100)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * Many Places one User
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User", inversedBy="moderated")
     * @ORM\JoinColumn(name="moderator", referencedColumnName="id")
     */
    private $moderator;

    /**
     * Many places many users
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\User", mappedBy="places")
     */
    private $users;

    /**
     * One place many items
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Item", mappedBy="place")
     */
    private $items;

    /**
     * @var Message[]
     *
     * One place many Messages
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Message", mappedBy="place")
     */
    private $messages;

    /***********************************/

    public function __construct()
    {
        $this->users = new ArrayCollection();
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @param mixed $moderator
     */
    public function setModerator($moderator)
    {
        $this->moderator = $moderator;
    }

    /**
     * @return mixed
     */
    public function getModerator()
    {
        return $this->moderator;
    }

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
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * @param mixed $users
     */
    private function setUsers($users)
    {
        $this->users = $users;
    }

    /**
     * @param User $user
     */
    public function addUser($user)
    {
        $this->users[] = $user;
    }

    /**
     * @param User $user
     */
    public function removeUser(User $user)
    {
        $this->users->removeElement($user);
    }

    /**
     * @return mixed
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @param mixed $items
     */
    public function setItems($items)
    {
        $this->items = $items;
    }

    /**
     * @return Message[]
     */
    public function getMessages()
    {
        return $this->messages;
    }
}
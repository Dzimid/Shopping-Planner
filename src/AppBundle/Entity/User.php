<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="fos_user")
 */
class User extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * One User many Places
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Place", mappedBy="moderator")
     */
    private $moderated;

    /**
     * Many users many places
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Place", inversedBy="users")
     * @ORM\JoinTable(name="users_places")
     */
    private $places;

    /**
     * @var Item[]
     *
     * One user many purchase
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Purchase", mappedBy="user")
     */
    private $boughtItems;

    /**
     * @var Message[]
     *
     * One user Many Messages
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Message", mappedBy="user")
     */
    private $messages;

    /*************************************/

    /**
     * User constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->moderated = new ArrayCollection();
        $this->places = new ArrayCollection();
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getModerated()
    {
        return $this->moderated;
    }

    /**
     * @return mixed
     */
    public function getPlaces()
    {
        return $this->places;
    }

    /**
     * @param Place $place
     */
    public function addPlace($place)
    {
        $place->addUser($this);
        $this->places[] = $place;
    }

    /**
     * Remove place
     *
     * @param $place
     * @return bool
     */
    public function removePlace($place)
    {
        return $this->places->removeElement($place);
    }

    /**
     * @param mixed $places
     */
    private function setPlaces($places)
    {
        $this->places = $places;
    }

    /**
     * @return Item[] | ArrayCollection
     */
    public function getBoughtItems()
    {
        return $this->boughtItems;
    }
}
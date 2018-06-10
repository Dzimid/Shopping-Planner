<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class Item
 * @package AppBundle\Entity
 *
 * @ORM\Entity()
 * @ORM\Table(name="item")
 */
class Item
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
     * @var Place
     *
     * Many item one place
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Place", inversedBy="items")
     * @ORM\JoinColumn(name="place", referencedColumnName="id")
     */
    private $place;

    /**
     * @var User[]
     *
     * One item many purchase
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Purchase", mappedBy="item")
     */
    private $boughtBy;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $mark;

    /****************************************/

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
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return Place
     */
    public function getPlace()
    {
        return $this->place;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @param Place $place
     */
    public function setPlace($place)
    {
        $this->place = $place;
    }

    /**
     * @return User[] | ArrayCollection
     */
    public function getBoughtBy()
    {
        return $this->boughtBy;
    }

    /**
     * @return int
     */
    public function getMark()
    {
        return $this->mark;
    }

    /**
     * @param int $mark
     */
    public function setMark($mark)
    {
        $this->mark = $mark;
    }
}
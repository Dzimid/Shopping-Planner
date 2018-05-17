<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class Purchase
 * @package AppBundle\Entity
 *
 * @ORM\Entity(repositoryClass="PurchaseRepository")
 * @ORM\Table(name="purchase")
 */
class Purchase
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
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=false)
     */
    private $date;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $comment;

    /**
     * @var Item
     *
     * Many Purchase one item
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Item", inversedBy="boughtBy")
     * @ORM\JoinColumn(name="item", referencedColumnName="id")
     */
    private $item;

    /**
     * @var User
     *
     * Many purchase one user
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User", inversedBy="boughtItems")
     * @ORM\JoinColumn(name="user", referencedColumnName="id")
     */
    private $user;

    /**********************************/

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
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @return Item
     */
    public function getItem()
    {
        return $this->item;
    }


    /**
     * @param string $comment
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
    }

    /**
     * @param \DateTime $date
     */
    public function setDate($date)
    {
        $this->date = $date;
    }

    /**
     * @param Item $item
     */
    public function setItem($item)
    {
        $this->item = $item;
    }
}
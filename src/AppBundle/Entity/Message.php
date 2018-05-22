<?php
/**
 * Created by PhpStorm.
 * User: szpar
 * Date: 22.05.2018
 * Time: 10:36
 */

namespace AppBundle\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class Message
 * @package AppBundle\Entity
 *
 * @ORM\Entity()
 * @ORM\Table(name="message")
 */
class Message
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
     * @ORM\Column(type="text", nullable=false)
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
     * Many Messages one user
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User", inversedBy="messages")
     * @ORM\JoinColumn(name="user", referencedColumnName="id")
     */
    private $user;

    /**
     * @var Place
     *
     * Many Messages one place
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Place", inversedBy="messages")
     * @ORM\JoinColumn(name="place", referencedColumnName="id")
     */
    private $place;
}
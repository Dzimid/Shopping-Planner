<?php

namespace AppBundle\Twig\Extension;


use AppBundle\Entity\Alert;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityManager;

class GlobalsExtension extends \Twig_Extension implements \Twig_Extension_GlobalsInterface
{
    protected $em;

    public function __construct(EntityManager $entityManager)
    {
        $this->em = $entityManager;
    }

    public function getGlobals()
    {
        $user = $this->em->getRepository(User::class)->find(1);
        return array (
            "newAlerts" => $this->em->getRepository(Alert::class)->getNotReadNum($user),
        );
    }

    public function getName()
    {
        return "AppBundle:GlobalsExtension";
    }
}
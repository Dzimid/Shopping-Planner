<?php

namespace AppBundle\Twig\Extension;


use AppBundle\Entity\Alert;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class GlobalsExtension extends \Twig_Extension implements \Twig_Extension_GlobalsInterface
{
    protected $em;
    protected $tokenStorage;

    public function __construct(EntityManager $entityManager, TokenStorageInterface $tokenStorage)
    {
        $this->em = $entityManager;
        $this->tokenStorage = $tokenStorage;
    }

    public function getGlobals()
    {
        $user = $this->tokenStorage->getToken()->getUser();

        if ($user instanceof User) {
            $newAlerts = $this->em->getRepository(Alert::class)->getNotReadNum($user);
        } else {
            $newAlerts = 0;
        }

        return array (
            "newAlerts" => $newAlerts,
            'asd' => 'asdasd'
        );
    }

    public function getName()
    {
        return "AppBundle:GlobalsExtension";
    }
}
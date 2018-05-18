<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Place;
use AppBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ModeratorController extends Controller
{
    public function createPlaceAction($formData)
    {
        $newPlace = new Place();
        $newPlace->setModerator($this->getUser());
        $newPlace->setName($formData['name']);
        $newPlace->setDescription($formData['description']);

        $em = $this->getDoctrine()->getManager();
        $em->persist($newPlace);
        $em->flush();

        $this->addFlash('newPlace', 'Dodano nowe miejsce');
        return $this->redirectToRoute('places_page');
    }

    public function addUserToPlaceAction($place, $formData)
    {
        $this->denyAccessUnlessGranted('edit', $place);

        $user = $this->getDoctrine()
            ->getRepository(User::class)
            ->findOneByUsername($formData['user_name']);

        if (empty($user)) {
            $this->addFlash('addUserToPlaceERROR', 'Niepoprawny użytkownik');
        } else if ($user->getId() == $this->getUser()->getId()) {
            $this->addFlash('addUserToPlaceERROR', 'Nie możesz dodać siebie do tej grupy');
        } else {
            $place->setUsers(array($user));
            $user->setPlaces(array($place));
            $em = $this->getDoctrine()->getManager();
            $em->persist($place);
            $em->persist($user);
            $em->flush();

            $this->addFlash('addUserToPlaceOK', "Dodano użytkownika {$user->getUsername()} do tego miejsca");
        }

        return $this->redirectToRoute('place_page', array('id' => $place->getId()));
    }
}

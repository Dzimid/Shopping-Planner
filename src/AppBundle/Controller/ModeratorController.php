<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Item;
use AppBundle\Entity\Place;
use AppBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ModeratorController extends Controller
{
    /**
     * Create Place Action
     *
     * @param array $formData (name, description)
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function createPlaceAction($formData)
    {
        $newPlace = new Place();
        $newPlace->setModerator($this->getUser());
        $newPlace->setName($formData['name']);
        $newPlace->setDescription($formData['description']);

        $user = $this->getDoctrine()->getRepository(User::class)->find($this->getUser());
        $user->addPlace($newPlace);
        $newPlace->addUser($user);

        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->persist($newPlace);
        $em->flush();

        $this->addFlash('success', 'Dodano nowe miejsce');
        return $this->redirectToRoute('places_page');
    }

    /**
     * Add User to Place Action
     *
     * @param Place $place
     * @param array $formData (user_name)
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function addUserToPlaceAction($place, $formData)
    {
        $this->denyAccessUnlessGranted('edit', $place);

        /** @var User $user */
        $user = $this->getDoctrine()
            ->getRepository(User::class)
            ->findOneBy(array('username' => $formData['user_name']));

        if (empty($user)) {
            $this->addFlash('danger', 'Niepoprawny użytkownik');
        } else if ($user->getId() == $this->getUser()->getId()) {
            $this->addFlash('danger', 'Nie możesz dodać siebie do tej grupy');
        } else {
            $user->addPlace($place);
            $place->addUser($user);

            $em = $this->getDoctrine()->getManager();
            $em->persist($place);
            $em->persist($user);
            $em->flush();

            $this->addFlash('success', "Dodano użytkownika {$user->getUsername()} do tego miejsca");
        }

        return $this->redirectToRoute('place_page', array('id' => $place->getId()));
    }

    /**
     * Add Item to Place Action
     *
     * @param Place $place
     * @param array $formData (name)
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function addItemToPlaceAction($place, $formData)
    {
        $this->denyAccessUnlessGranted('edit', $place);

        $p = new Item();
        $p->setName($formData['name']);
        $p->setPlace($place);

        $em = $this->getDoctrine()->getManager();
        $em->persist($p);
        $em->flush();

        $this->addFlash('success', 'Dodano przedmiot do tego miejca');
        return $this->redirectToRoute('place_page', array('id' => $place->getId()));
    }

    /**
     * Remove User from Place Action
     *
     * @param int $placeId
     * @param int $userId
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function removeUserFromPlaceAction($placeId, $userId)
    {
        if ($userId != $this->getUser()->getId()) {
            $place = $this->getDoctrine()
                ->getRepository(Place::class)
                ->find($placeId);

            $this->denyAccessUnlessGranted('edit', $place);

            $user = $this->getDoctrine()
                ->getRepository(User::class)
                ->find($userId);

            $user->removePlace($place);
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            // TODO Usuwanie wszystkich przedmiotów kupionych przez usuwanego użytkownika w danym miejscu

            $this->addFlash('info', 'Usunięto użytkownika ' . $user->getUsername());
        }

        return $this->redirectToRoute('place_page', array('id' => $placeId));
    }

    /**
     * Remove Item from Place Action
     *
     * @param int $placeId
     * @param int $itemId
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function removeItemFromPlaceAction($placeId, $itemId)
    {
        $item = $this->getDoctrine()
            ->getRepository(Item::class)
            ->find($itemId);

        $this->denyAccessUnlessGranted('edit', $item);

        $em = $this->getDoctrine()->getManager();
        $em->remove($item);
        $em->flush();

        $this->addFlash('info', 'Usunięto przedmiot');
        return $this->redirectToRoute('place_page', array('id' => $placeId));
    }
}

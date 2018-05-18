<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Item;
use AppBundle\Entity\Place;
use AppBundle\Entity\Purchase;
use AppBundle\Entity\User;
use AppBundle\Form\AddItemToPlaceForm;
use AppBundle\Form\AddUserToPlaceForm;
use AppBundle\Form\PlaceForm;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render("index.html.twig");
    }

    public function mapAction()
    {
        return $this->render("map.html.twig");
    }

    public function placesAction(Request $request)         // TODO: Zabezpieczyć tę akcje
    {
        $user = $this->getDoctrine()
            ->getRepository(User::class)
            ->find($this->getUser());

        $moderated = array();
        $places = array();

        foreach ($user->getModerated() as $place) {
            $moderated[] = array('name' => $place->getName(), 'id' => $place->getid());
        }
        foreach ($user->getPlaces() as $place) {
            $places[] = array('name' => $place->getName(), 'id' => $place->getId());
        }

        //FORM

        $form = $this->createForm(PlaceForm::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->forward('AppBundle:Moderator:createPlace', array(
                'formData' => $form->getData()
            ));
        }

        return $this->render('places.html.twig', array('moderated' => $moderated, 'places' => $places, 'form' => $form->createView()));
    }

    public function placeAction($id, Request $request)        // TODO: Zabezpieczyć tę akcje
    {
        $place = $this->getDoctrine()
            ->getRepository(Place::class)
            ->find($id);

        $purchase = $this->getDoctrine()
            ->getRepository(Purchase::class)
            ->getAllPurchaseByPlace($place);

        $usersInPlace = array();
        $itemsInPlace = array();
        $purchasePerUser = array(array());

        foreach ($place->getUsers() as $usr) {
            $usersInPlace[] = array(
                'name' => $usr->getUsername(),
                'id' => $usr->getId()
            );
        }
        foreach ($place->getItems() as $itm) {
            $itemsInPlace[] = array(
                'name' => $itm->getName(),
                'id' => $itm->getId()
            );
        }
        foreach ($purchase as $p) {
            $purchasePerUser[$p->getUser()->getId()][$p->getItem()->getId()][] = $p->getDate()->format('Y-m-d H:i:s');
        }

        $placeInfo = array(
            'id' => $place->getId(),
            'name' => $place->getName(),
            'description' => $place->getDescription(),
            'moderator' => $place->getModerator(),
        );

        /***USER FORM***/

        $userForm = $this->createForm(AddUserToPlaceForm::class);
        $userForm->handleRequest($request);

        if ($userForm->isSubmitted() && $userForm->isValid()) {
            return $this->forward('AppBundle:Moderator:addUserToPlace', array(
                'place' => $place,
                'formData' => $userForm->getData(),
            ));
        }

        /***ITEM FORM***/

        $itemForm = $this->createForm(AddItemToPlaceForm::class);
        $itemForm->handleRequest($request);

        if ($itemForm->isSubmitted() && $itemForm->isValid()) {
            return $this->forward('AppBundle:Moderator:addItemToPlace', array(
                'place' => $place,
                'formData' => $itemForm->getData()
            ));
        }

        return $this->render('place.html.twig',
            array(
                'placeInfo' => $placeInfo,
                'userForm' => $userForm->createView(),
                'itemForm' => $itemForm->createView(),
                'usersInPlace' => $usersInPlace,
                'itemsInPlace' => $itemsInPlace,
                'purchasePerUser' => $purchasePerUser
            )
        );
    }

    public function addPurchaseAction($i_id)
    {
        $item = $this->getDoctrine()
            ->getRepository(Item::class)
            ->find($i_id);

        $purchase = new Purchase();
        $purchase->setDate(new \DateTime());
        $purchase->setUser($this->getUser());
        $purchase->setItem($item);

        $em = $this->getDoctrine()->getManager();
        $em->persist($purchase);
        $em->flush();

        $this->addFlash('addPurchase', 'Dodano zakup');
        return $this->redirectToRoute('place_page', array('id' => $item->getPlace()->getId()));
    }
}

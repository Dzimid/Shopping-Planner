<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Item;
use AppBundle\Entity\Message;
use AppBundle\Entity\Place;
use AppBundle\Entity\Purchase;
use AppBundle\Entity\User;
use AppBundle\Form\AddItemToPlaceForm;
use AppBundle\Form\AddMessageForm;
use AppBundle\Form\AddUserToPlaceForm;
use AppBundle\Form\PlaceForm;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * Index Action
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        return $this->render("index.html.twig");
    }

    /**
     * Map action
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function mapAction()
    {
        return $this->render("map.html.twig");
    }

    /**
     * Places Action
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function placesAction(Request $request)         // TODO: Zabezpieczyć tę akcje
    {
        $user = $this->getDoctrine()
            ->getRepository(User::class)
            ->find($this->getUser());

        $moderated = array();
        $places = array();

        /** @var Place $place */
        foreach ($user->getModerated() as $place) {
            $moderated[] = array('name' => $place->getName(), 'id' => $place->getid());
        }

        /** @var Place $place */
        foreach ($user->getPlaces() as $place) {
            $places[] = array('name' => $place->getName(),  'id' => $place->getId());
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

    /**
     * Place action
     *
     * @param int $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
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

        /** @var User $usr */
        foreach ($place->getUsers() as $usr) {
            $usersInPlace[] = array(
                'name' => $usr->getUsername(),
                'id' => $usr->getId()
            );
        }

        /** @var Item $itm */
        foreach ($place->getItems() as $itm) {
            $itemsInPlace[] = array(
                'name' => $itm->getName(),
                'id' => $itm->getId()
            );
        }

        /** @var Purchase $p */
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

    /**
     * Add Purchase action
     *
     * @param int $itemId
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function addPurchaseAction($itemId)
    {
        /** @var Item $item */
        $item = $this->getDoctrine()
            ->getRepository(Item::class)
            ->find($itemId);

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

    /**
     * Messages Action
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function messagesAction($id, Request $request)
    {
        $place = $this->getDoctrine()
            ->getRepository(Place::class)
            ->find($id);

        $messages = array();

        /** @var Message $message */
        foreach ($place->getMessages() as $message) {
            $messages[] = array(
                'content' => $message->getContent(),
                'author' => $message->getUser()->getUsername(),
                'date' => $message->getDate()->format("Y-m-d H:i:s")
            );
        }

        $form = $this->createForm(AddMessageForm::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // TODO Przenieśc tę logike do nowej akcji
            $msg = new Message();
            $msg->setContent($form->getData()['message']);
            $msg->setDate(new \DateTime());
            $msg->setUser($this->getUser());
            $msg->setPlace($place);

            $em = $this->getDoctrine()->getManager();
            $em->persist($msg);
            $em->flush();

            $this->addFlash('addMessage', 'Dodano wiadomość');
            return $this->redirectToRoute('messages_page', array('id' => $id));
        }

        return $this->render('messages.html.twig', array(
            'form' => $form->createView(),
            'messages' => array_reverse($messages)
        ));
    }
}

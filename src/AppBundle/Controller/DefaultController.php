<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Alert;
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
     *
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
            if ($place->getModerator() != $this->getUser()) {
                $places[] = array('name' => $place->getName(), 'id' => $place->getId());
            }
        }

        //FORM

        $form = $this->createForm(PlaceForm::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->forward('AppBundle:Moderator:createPlace', array(
                'formData' => $form->getData()
            ));
        }

        return $this->render('places.html.twig', array(
            'moderated' => $moderated,
            'places' => $places,
            'form' => $form->createView()));
    }

    /**
     * Place action
     *
     * @param Place   $place
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function placeAction(Place $place, Request $request)        // TODO: Zabezpieczyć tę akcje
    {
        $em = $this->getDoctrine()->getManager();

        $purchase = $em
            ->getRepository(Purchase::class)
            ->getAllPurchaseByPlace($place);
        $purchasePerUser = array(array());
        $latestPurchase = array();

        /** @var Item $item */
        foreach ($place->getItems() as $item) {
            $latestPurchase[$item->getId()] = $em
                ->getRepository(Purchase::class)
                ->getLatestPurchase($item, $place);
        }

        /** @var Purchase $p */
        foreach ($purchase as $p) {
            $date = $p->getDate();
            $currentDate = new \DateTime();
            $diff = $currentDate->diff($date);

            switch ($diff->days) {
                case 0:
                    $dt = 'Dzisiaj';
                    break;
                case 1:
                    $dt = 'Wczoraj';
                    break;
                default:
                    $dt = $diff->days . ' dni temu';
            }

            $purchasePerUser[$p->getUser()->getId()][$p->getItem()->getId()][] = [
                $dt,
                $p->getId(),
            ];
        }

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
                'placeInfo' => $place,
                'userForm' => $userForm->createView(),
                'itemForm' => $itemForm->createView(),
                'usersInPlace' => $place->getUsers(),
                'itemsInPlace' => $place->getItems(),
                'purchasePerUser' => $purchasePerUser,
                'latest' => $latestPurchase,
            )
        );
    }

    /**
     * Add Purchase action
     *
     * @param Item $item
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function addPurchaseAction(Item $item)
    {
        $item->setMark(0);
        $purchase = new Purchase();
        $purchase->setDate(new \DateTime());
        $purchase->setUser($this->getUser());
        $purchase->setItem($item);

        $em = $this->getDoctrine()->getManager();
        $em->persist($purchase);
        $em->persist($item);
        $em->flush();

        $this->addFlash('success', 'Dodano zakup');
        return $this->redirectToRoute('place_page', array('place' => $item->getPlace()->getId()));
    }

    /**
     * @param Purchase $purchase
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function removePurchaseAction(Purchase $purchase, Place $place)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($purchase);
        $em->flush();

        return $this->redirectToRoute('place_page', [
            'place' => $place->getId()
        ]);
    }

    /**
     * Messages Action
     *
     * @param Place   $place
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function messagesAction(Place $place, Request $request)
    {
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

            $this->addFlash('success', 'Dodano wiadomość');
            return $this->redirectToRoute('messages_page', array('place' => $place->getId()));
        }

        return $this->render('messages.html.twig', array(
            'form' => $form->createView(),
            'messages' => array_reverse($messages)
        ));
    }


    /**
     * @param Item  $item
     * @param Place $place
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function markItemAction(Item $item, Place $place)
    {
        $em = $this->getDoctrine()->getManager();

        $latestPurchase = $em
            ->getRepository(Purchase::class)
            ->getLatestPurchase($item, $place);
        $to = $em
            ->getRepository(User::class)
            ->find($latestPurchase);

        $item->setMark(1);
        $alert = new Alert('Przypomnienie o produkcie', new \DateTime(), $to, $this->getUser());

        $em->persist($item);
        $em->persist($alert);
        $em->flush();

        return $this->redirectToRoute('place_page', ['place' => $place->getId()]);
    }

    /**
     * @param Item  $item
     * @param Place $place
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function unmarkItemAction(Item $item, Place $place)
    {
        $item->setMark(0);

        $em = $this->getDoctrine()->getManager();
        $em->persist($item);
        $em->flush();

        return $this->redirectToRoute('place_page', ['place' => $place->getId()]);
    }

    /**
     * Alert list Action
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function alertListAction()
    {
        $alerts = $this
            ->getDoctrine()
            ->getRepository(Alert::class)
            ->findBy([
                'user' => $this->getUser()
            ]);

        return $this->render('alertList.html.twig', [
            'alerts' => $alerts
        ]);
    }

    /**
     * Alert remove Action
     *
     * @param Alert $alert
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function alertRemoveAction(Alert $alert)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($alert);
        $em->flush();

        return $this->redirectToRoute('alertList');
    }
}

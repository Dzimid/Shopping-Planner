<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Place;
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

    public function placesAction(Request $request)
    {
        $place = new Place();
        $form = $this->createForm(PlaceForm::class, $place);
        $form->handleRequest($request);
        $resp = "";

        if ($form->isSubmitted() && $form->isValid()) {
            $userId = $this->getUser();

            if (!empty($userId)) {
                $place = $form->getData();
                $place->setModerator($this->getUser());

                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($place);
                $entityManager->flush();

                $resp = "Dodano miejsce id = " . $place->getId();
            } else {
                $resp = "Zaloguj się";
            }
        }

        return $this->render('places.html.twig', array('form' => $form->createView(), 'resp' => $resp));
    }
}

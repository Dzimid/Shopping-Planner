<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Place;
use AppBundle\Entity\User;
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

        $places = array();

        foreach ($user->getPlaces() as $place) {
            $places[] = array('name' => $place->getName(), 'id' => $place->getid());
        }

        //FORM

        $form = $this->createForm(PlaceForm::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newPlace = new Place();
            $newPlace->setModerator($this->getUser());
            $newPlace->setName($form->getData()['name']);
            $newPlace->setDescription($form->getData()['description']);

            $em = $this->getDoctrine()->getManager();
            $em->persist($newPlace);
            $em->flush();

            $this->addFlash('newPlace', 'Dodano nowe miejsce');
            return $this->redirectToRoute('places_page');
        }

        return $this->render('places.html.twig', array('places' => $places, 'form' => $form->createView()));
    }

    public function placeAction($id)        // TODO: Zabezpieczyć tę akcje
    {
        $place = $this->getDoctrine()
            ->getRepository(Place::class)
            ->find($id);

        $placeInfo = array(
            'name' => $place->getName(),
            'description' => $place->getDescription(),
            'moderator' => $place->getModerator()
        );

        return $this->render('place.html.twig', array('placeInfo' => $placeInfo));
    }
}

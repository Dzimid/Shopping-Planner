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

    public function placesAction()
    {
        $place = new Place();
        $form = $this->createForm(PlaceForm::class, $place);

        return $this->render('places.html.twig', array('form' => $form->createView()));
    }
}

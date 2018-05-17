<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Place;
use AppBundle\Entity\User;
use AppBundle\Form\PlaceForm;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
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
            // TODO Przenieść tę logikę w inne miejsce

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

        return $this->render('places.html.twig', array('moderated' => $moderated, 'places' => $places, 'form' => $form->createView()));
    }

    public function placeAction($id, Request $request)        // TODO: Zabezpieczyć tę akcje
    {
        $place = $this->getDoctrine()
            ->getRepository(Place::class)
            ->find($id);

        $usersInPlace = array();

        foreach ($place->getUsers() as $usr) {
            $usersInPlace[] = array(
                'name' => $usr->getUsername(),
                'id' => $usr->getId()
            );
        }

        $placeInfo = array(
            'id' => $place->getId(),
            'name' => $place->getName(),
            'description' => $place->getDescription(),
            'moderator' => $place->getModerator(),
        );

        $form = $this->createFormBuilder()
            ->add('user_name', TextType::class, array('label' => 'Dodaj użytkownika', 'required' => true))
            ->add('add', SubmitType::class, array('label' => 'Dodaj', 'attr' => array('class' => 'btn btn-success')))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // TODO Przenieść tę logike w inne miejsce

            $this->denyAccessUnlessGranted('edit', $place);

            $em = $this->getDoctrine()->getManager();
            $user = $this->getDoctrine()
                ->getRepository(User::class)
                ->findOneByUsername($form->getData()['user_name']);

            if (empty($user)) {
                $this->addFlash('addUserToPlaceERROR', 'Niepoprawny użytkownik');
            } else if ($user->getId() == $this->getUser()->getId()) {
                $this->addFlash('addUserToPlaceERROR', 'Nie możesz dodać siebie do tej grupy');
            } else {
                $place->setUsers(array($user));
                $user->setPlaces(array($place));
                $em->persist($place);
                $em->persist($user);
                $em->flush();

                $this->addFlash('addUserToPlaceOK', "Dodano użytkownika {$user->getUsername()} do tego miejsca");
                return $this->redirectToRoute('place_page', array('id' => $id));
            }
        }

        return $this->render('place.html.twig',
            array(
                'placeInfo' => $placeInfo,
                'form' => $form->createView(),
                'usersInPlace' => $usersInPlace
            )
        );
    }

    public function removeUserFromPlaceAction($p_id, $u_id)
    {
        $user = $this->getDoctrine()
            ->getRepository(User::class)
            ->find($u_id);

        $place = $this->getDoctrine()
            ->getRepository(Place::class)
            ->find($p_id);

        $this->denyAccessUnlessGranted('edit', $place);

        $user->removePlace($place);
        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();

        $this->addFlash('addUserToPlaceOK', 'Usunięto użytkownika ' . $user->getUsername());
        return $this->redirectToRoute('place_page', array('id' => $p_id));
    }
}

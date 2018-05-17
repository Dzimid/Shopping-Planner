<?php

namespace AppBundle\Security;

use AppBundle\Entity\Place;
use AppBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class PlaceVoter extends Voter
{
    const VIEW = 'view';
    const EDIT = 'edit';

    protected function supports($attribute, $subject)
    {
        if (!in_array($attribute, array(self::VIEW, self::EDIT))) {
            return false;
        }

        if (!($subject instanceof Place)) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();

        if (!($user instanceof User)) {
            return false;
        }

        switch ($attribute) {
            case self::VIEW:
                return $this->canView($subject, $user);
            case self::EDIT:
                return $this->canEdit($subject, $user);
        }

        return false;
    }

    private function canView(Place $place, User $user)
    {
        // Jeżeli może edytować, to może i wyświetlać
        if ($this->canEdit($place, $user)) {
            return true;
        }

        // TODO implementacja miejsc prywatnych
        return true;
    }

    private function canEdit(Place $place, User $user)
    {
        return $user === $place->getModerator();
    }
}
<?php
/**
 * Created by PhpStorm.
 * User: szpar
 * Date: 17.05.2018
 * Time: 13:47
 */

namespace AppBundle\Security;


use AppBundle\Entity\Item;
use AppBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ItemVoter extends Voter
{
    const VIEW = 'view';
    const EDIT = 'edit';

    protected function supports($attribute, $subject)
    {
        if (!in_array($attribute, array(self::VIEW, self::EDIT))) {
            return false;
        }

        if (!($subject instanceof Item)) {
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

    private function canView(Item $item, User $user)
    {
        // Jeżeli może edytować, to może i wyświetlać
        if ($this->canEdit($item, $user)) {
            return true;
        }

        // TODO Przedmioty prywatne (feature)
        return true;
    }

    private function canEdit(Item $item, User $user)
    {
        // Można edytować przedmioty z miejsc, w których jest się moderatorem
        return $user === $item->getPlace()->getModerator();
    }
}
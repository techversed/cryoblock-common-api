<?php

namespace Carbon\ApiBundle\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class CommentVoter implements VoterInterface
{

    public function supportsAttribute($attribute)
    {
        var_dump('supports');
    }

    public function vote(TokenInterface $token, $object, array $attributes)
    {
        if (!is_array($object) && !is_object($object[0])) {
            return true;
        }
        return true;

    }

    public function supportsClass($class)
    {
        return true;
    }
}

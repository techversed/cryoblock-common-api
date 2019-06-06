<?php

namespace Carbon\ApiBundle\Listener\Help;

use AppBundle\Entity\Help\Help;
use Doctrine\ORM\EntityManager;
use JMS\Serializer\EventDispatcher\Events;
use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\ObjectEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class BaseHelpSerializerListener implements EventSubscriberInterface
{
    public function __construct(EntityManager $em, TokenStorage $tokenStorage)
    {
        $this->em = $em;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            // array('event' => Events::PRE_SERIALIZE, 'method' => 'onPreSerialize', 'format' => 'json'),
            array(
                'event' => Events::POST_SERIALIZE,
                'method' => 'onPostSerialize',
                'format' => 'json'
            ),
        );
    }

    public function onPostSerialize(ObjectEvent $event)
    {
        $obj = $event->getObject();

        if ($obj instanceof Help) {

            $user = $this->tokenStorage->getToken()->getUser();

            $help = $obj;

            $repo = $this->em->getRepository('AppBundle\\Entity\\Help\\Help');

            $canView = $repo->canUserView($help, $user);
            $canEdit = $repo->canUserEdit($help, $user);

            $event->getVisitor()->setData('canView', $canView);
            $event->getVisitor()->setData('canEdit', $canEdit);



        }

    }
}

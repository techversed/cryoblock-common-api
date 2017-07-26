<?php

namespace Carbon\ApiBundle\Listener\Storage;

use AppBundle\Entity\Storage\Division;
use Doctrine\ORM\EntityManager;
use JMS\Serializer\EventDispatcher\Events;
use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\ObjectEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class BaseDivisionSerializerListener implements EventSubscriberInterface
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

        if ($obj instanceof Division) {

            $user = $this->tokenStorage->getToken()->getUser();

            $division = $obj;

            $repo = $this->em->getRepository('AppBundle\\Entity\\Storage\\Division');

            $canView = $repo->canUserView($division, $user);
            $canEdit = $repo->canUserEdit($division, $user);

            $event->getVisitor()->setData('canView', $canView);
            $event->getVisitor()->setData('canEdit', $canEdit);

            if (!$canView) {
                $event->getVisitor()->setData('samples', array());
            }

        }

    }
}

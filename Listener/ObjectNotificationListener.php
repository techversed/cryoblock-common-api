<?php

namespace Carbon\ApiBundle\Listener;

use Carbon\ApiBundle\Entity\Comment;
use Carbon\ApiBundle\Service\CryoblockMailer;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;

class ObjectNotificationListener
{
    public function __construct(CryoblockMailer $mailer, Logger $logger, $frontendUrl)
    {
        $this->mailer = $mailer;
        $this->logger = $logger;
        $this->frontendUrl = $frontendUrl;
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $em = $args->getEntityManager();

        $objectType = $this->getObjectType($entity);

        $objectNotification = $this->getObjectNotification($em, $objectType);

        if ($objectNotification === NULL) {
            return;
        }

        $createGroup = $objectNotification->getOnCreateGroup();

        if ($createGroup === NULL) {
            return;
        }

        $this->sendCreateEmail($entity, $objectNotification, $createGroup);
    }

    public function postUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $em = $args->getEntityManager();

        $objectType = $this->getObjectType($entity);

        $objectNotification = $this->getObjectNotification($em, $objectType);

        if ($objectNotification === NULL) {
            return;
        }

        $updateGroup = $objectNotification->getOnUpdateGroup();

        if ($updateGroup === NULL) {
            return;
        }

        $this->sendUpdateEmail($entity, $objectNotification, $updateGroup);
    }

    public function postRemove(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $em = $args->getEntityManager();

    }

    private function getObjectType($entity)
    {
        $nameConverter = new CamelCaseToSnakeCaseNameConverter();
        $className = lcfirst($this->getClassShortName($entity));

        return $nameConverter->normalize($className);
    }

    private function getClassShortName($entity)
    {
        $reflect = new \ReflectionClass(get_class($entity));

        return $reflect->getShortName();
    }

    private function getObjectNotification($em, $objectType)
    {
        return $em->getRepository('CarbonApiBundle:ObjectNotification')->findOneBy(array(
            'objectType' => $objectType
        ));
    }

    private function sendCreateEmail($entity, $objectNotification, $group, $params = array())
    {
        $toUsers = $this->getUsersFromGroup($group);

        $this->mailer->send(
            sprintf('[cryoblock] %s %s Created', $this->getClassShortName($entity), $entity->getId()),
            'CarbonApiBundle::objectNotification/create.html.twig',
            $toUsers,
            $params
        );
    }

    private function sendUpdateEmail($entity, $objectNotification, $group, $params = array())
    {
        $toUsers = $this->getUsersFromGroup($group);

        $this->mailer->send(
            sprintf('[cryoblock] %s %s Updated', $this->getClassShortName($entity), $entity->getId()),
            'CarbonApiBundle::objectNotification/update.html.twig',
            $toUsers,
            $params
        );
    }

    private function getUsersFromGroup($group)
    {
        $users = array();
        foreach ($group->getGroupUsers() as $groupUser) {
            $user = $groupUser->getUser();
            $users[$user->getEmail()] = $user->getFullName();
        }

        return $users;
    }
}

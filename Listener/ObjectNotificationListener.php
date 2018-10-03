<?php

namespace Carbon\ApiBundle\Listener;

use Carbon\ApiBundle\Entity\Comment;
use Carbon\ApiBundle\Entity\Production\BaseRequest;
use Carbon\ApiBundle\Service\CryoblockMailer;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;

use Carbon\ApiBundle\Entity\UserObjectNotification;

class ObjectNotificationListener
{
    public function __construct(CryoblockMailer $mailer, Logger $logger, $mailerUser, $tokenStorage, $frontendUrl, $appName)
    {
        $this->mailer = $mailer;
        $this->logger = $logger;
        $this->mailerUser = $mailerUser;
        $this->tokenStorage = $tokenStorage;
        $this->frontendUrl = $frontendUrl;
        $this->appName = $appName;
    }

    //A list of entity classes for which updates will not be sent.
        //Example: You would not want to send notifications when notification settings were updated... //also prevents the autowatching that takes place when someone creates an object.
    public $ignoreClasses = array(
        'Carbon\ApiBundle\Entity\UserObjectNotification',
        'Carbon\ApiBundle\Entity\EntityDetail'
    );

    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $em = $args->getEntityManager();
        $uow = $em->getUnitOfWork();

        if (in_array(get_class($entity), $this->ignoreClasses)) {
            return;
        }

        if (!$this->tokenStorage->getToken()) {
            return;
        }

        $creatingUser = $this->tokenStorage->getToken()->getUser();


        $entDet = $em->getRepository('Carbon\ApiBundle\Entity\EntityDetail')->findOneBy(array(
            'objectClassName' => get_class($entity)
        ));

        if (!$entDet instanceof EntityDetail || $entDet->getAutoWatch() == false) { //We have chosen to populate the Entity Detail table from the front end. If the entry does not exist then we are just going to exit.

            return;

        }

        $entDetId = $entDet->getId();

        $groupObjectNotification = $em->getRepository('Carbon\ApiBundle\Entity\GroupObjectNotification') //Have not even changed this yet...
            ->findOneBy(array(
                'entityDetailId' => $entDetId
            ))
        ;

        $userObjectNotifications = $em->getRepository('Carbon\ApiBundle\Entity\UserObjectNotification')
            ->findBy(array(
                'entityDetailId' => $entDetId,
                'entityId' => null
            ))
        ;

        $creatingUserObjectNotification = new UserObjectNotification();
        $creatingUserObjectNotification->setEntityId($entity->getId());
        $creatingUserObjectNotification->setEntityDetail($entDet);
        $creatingUserObjectNotification->setUser($creatingUser);
        $creatingUserObjectNotification->setOnUpdate(true);
        $creatingUserObjectNotification->setOnDelete(true);
        $em->persist($creatingUserObjectNotification);

        $groups = array();
        if ($groupObjectNotification && $onCreateGroup = $groupObjectNotification->getOnCreateGroup()) {
            $groups[] = $onCreateGroup->getName();
            $url = $groupObjectNotification->getEntityDetail()->getObjectUrl();
            $objectDescription = $groupObjectNotification->getEntityDetail()->getObjectDescription();
        }

        $to = array();
        foreach ($userObjectNotifications as $userObjectNotification) {
            if ($userObjectNotification->getOnCreate() && $userObjectNotification->getUser() != $creatingUser) {
                $to[$userObjectNotification->getUser()->getEmail()] = $userObjectNotification->getUser()->getFullName();
                $url = $userObjectNotification->getEntityDetail()->getObjectUrl();
                $objectDescription = $userObjectNotification->getEntityDetail()->getObjectDescription();
            }
        }

        if (!count($to) && !count($groups)) {
            $em->flush();
            return;
        }

        $annotationReader = new AnnotationReader();
        $reflectionClass = new \ReflectionClass(get_class($entity));
        $classAnnotations = $annotationReader->getClassAnnotations($reflectionClass);

        $changeSets = array('ID' => $entity->getId());
        foreach ($uow->getEntityChangeSet($entity) as $keyField => $field) {
            $reflectionProperty = new \ReflectionProperty(get_class($entity), $keyField);
            $propertyAnnotations = $annotationReader->getPropertyAnnotations($reflectionProperty);
            foreach ($propertyAnnotations as $propertyAnnotation) {
                if (get_class($propertyAnnotation) == 'Gedmo\Mapping\Annotation\Versioned') {
                    $fieldName = preg_replace('/[A-Z]/', ' ' . '\\0', $keyField);
                    $fieldName = ucfirst($fieldName);
                    $changeSets[$fieldName] = is_object($field[1]) ? (get_class($field[1]) == 'DateTime' ? $field[1]->format('Y-m-d') : $field[1]->getStringLabel()) : $field[1];
                }
            }
        }

        $now = new \DateTime();
        $changeSets['Created By'] = $creatingUser->getFullName();
        $changeSets['Created At'] = $now->format('Y-m-d H:i:s');

        $linkUrl = sprintf(
            "%s%s/%s",
            $this->frontendUrl,
            $url,
            $entity->getId()
        );

        $from = array($this->mailerUser => $this->appName);
        $objectDescription = sprintf(
            '%s %s',
            $objectDescription,
            ($entity instanceof BaseRequest) ? $entity->getAlias() : $entity->getId()
        );

        $em->flush();

        $this->mailer->send(
            $objectDescription . ' Created',
            'CarbonApiBundle:objectNotification:create.html.twig',
            $to,
            array(
                'creatingUser' => $creatingUser->getFullName(),
                'linkUrl' => $linkUrl,
                'changeSets' => $changeSets,
                'objectDescription' => $objectDescription,
            ),
            $from,
            $groups
        );
    }

    public function postUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $em = $args->getEntityManager();
        $uow = $em->getUnitOfWork();

        if (in_array(get_class($entity), $this->ignoreClasses)) {
            return;
        }

        if (!$this->tokenStorage->getToken()) {
            return;
        }

        $updatingUser = $this->tokenStorage->getToken()->getUser();

        $entDet = $em->getRepository('Carbon\ApiBundle\Entity\EntityDetail')->findOneBy(array(
            'objectClassName' => get_class($entity)
        ));

        if (!$entDet instanceOf EntityDetail) {
            return;
        }

        $entDetId = $entDet->getId();

        $groupObjectNotification = $em->getRepository('Carbon\ApiBundle\Entity\GroupObjectNotification')
            ->findOneBy(array(
                'entityDetailId' => $entDetId
            ))
        ;

        $userObjectNotifications = $em->getRepository('Carbon\ApiBundle\Entity\UserObjectNotification')
            ->findBy(array(
                'entityDetailId' => $entDetId,
                'entityId' => null
            ))
        ;

        $watchingUserNotifications = $em->getRepository('Carbon\ApiBundle\Entity\UserObjectNotification')
            ->findBy(array(
                'entityDetailId' => $entDetId,
                'entityId' => $entity->getId(),
            ))
        ;

        $groups = array();
        if ($groupObjectNotification && $onUpdateGroup = $groupObjectNotification->getOnUpdateGroup()) {
            $groups[] = $onUpdateGroup->getName();
            $url = $groupObjectNotification->getEntityDetail()->getObjectUrl();
            $objectDescription = $groupObjectNotification->getEntityDetail()->getObjectDescription();
        }

        $to = array();
        foreach ($userObjectNotifications as $userObjectNotification) {
            if ($userObjectNotification->getOnUpdate()) {
                $to[$userObjectNotification->getUser()->getEmail()] = $userObjectNotification->getUser()->getFullName();
                $url = $userObjectNotification->getEntityDetail()->getObjectUrl();
                $objectDescription = $userObjectNotification->getEntityDetail()->getObjectDescription();
            }
        }

        foreach ($watchingUserNotifications as $watchingUserNotification) {
            if ($watchingUserNotification->getOnUpdate()) {
                $to[$watchingUserNotification->getUser()->getEmail()] = $watchingUserNotification->getUser()->getFullName();
                $url = $watchingUserNotification->getEntityDetail()->getObjectUrl();
                $objectDescription = $watchingUserNotification->getEntityDetail()->getObjectDescription();
            }
        }

        if (!count($to) && !count($groups)) {
            return;
        }

        $annotationReader = new AnnotationReader();
        $reflectionClass = new \ReflectionClass(get_class($entity));
        $classAnnotations = $annotationReader->getClassAnnotations($reflectionClass);

        $changeSets = array();
        foreach ($uow->getEntityChangeSet($entity) as $keyField => $field) {

            $reflectionProperty = new \ReflectionProperty(get_class($entity), $keyField);
            $propertyAnnotations = $annotationReader->getPropertyAnnotations($reflectionProperty);

            if ($keyField == 'deletedAt' && $field[1] == null) {
                // object was restored
                $changeSets['Deleted At'] = array(
                    'oldValue' => is_object($field[0]) ? (get_class($field[0]) == 'DateTime' ? $field[0]->format('Y-m-d') : $field[0]->getStringLabel()) : $field[0],
                    'newValue' => 'Restored',
                );
            }

            foreach ($propertyAnnotations as $propertyAnnotation) {

                if (get_class($propertyAnnotation) == 'Gedmo\Mapping\Annotation\Versioned') {

                    $fieldName = preg_replace('/[A-Z]/', ' ' . '\\0', $keyField);
                    $fieldName = ucfirst($fieldName);

                    $changeSets[$fieldName] = array(
                        'oldValue' => is_object($field[0]) ? (get_class($field[0]) == 'DateTime' ? $field[0]->format('Y-m-d') : $field[0]->getStringLabel()) : $field[0],
                        'newValue' => is_object($field[1]) ? (get_class($field[1]) == 'DateTime' ? $field[1]->format('Y-m-d') : $field[1]->getStringLabel()) : $field[1],
                    );
                }
            }
        }

        if (count($changeSets) == 0) {
            return;
        }

        $linkUrl = sprintf(
            "%s%s/%s",
            $this->frontendUrl,
            $url,
            $entity->getId()
        );

        $from = array($this->mailerUser => $this->appName);
        $objectDescription = sprintf(
            '%s %s',
            $objectDescription,
            ($entity instanceof BaseRequest) ? $entity->getAlias() : $entity->getId()
        );
        $this->mailer->send(
            $objectDescription . ' Updated',
            'CarbonApiBundle:objectNotification:update.html.twig',
            $to,
            array(
                'updatingUser' => $updatingUser->getFullName(),
                'linkUrl' => $linkUrl,
                'changeSets' => $changeSets,
                'objectDescription' => $objectDescription,
            ),
            $from
            ,
            $groups

        );
    }

    public function postSoftDelete(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $em = $args->getEntityManager();
        $uow = $em->getUnitOfWork();

        if (in_array(get_class($entity), $this->ignoreClasses)) {
            return;
        }

        if (!$this->tokenStorage->getToken()) {
            return;
        }

        $deletingUser = $this->tokenStorage->getToken()->getUser();

        $entDet = $em->getRepository('Carbon\ApiBundle\Entity\EntityDetail')->findOneBy(array(
            'objectClassName' => get_class($entity)
        ));
        if (!$entDet instanceof EntityDetail) {
            return;
        }
        $entDetId = $entDet->getId();

        $groupObjectNotification = $em->getRepository('Carbon\ApiBundle\Entity\GroupObjectNotification')
            ->findOneBy(array(
                'entityDetailId' => $entDetId
            ))
        ;

        $userObjectNotifications = $em->getRepository('Carbon\ApiBundle\Entity\UserObjectNotification')
            ->findBy(array(
                'entityDetailId' => $entDetId,
                'entityId' => null
            ))
        ;

        $watchingUserNotifications = $em->getRepository('Carbon\ApiBundle\Entity\UserObjectNotification')
            ->findBy(array(
                'entityDetailId' => $entDetId,
                'entityId' => $entity->getId(),
            ))
        ;

        $groups = array();
        if ($groupObjectNotification && $onDeleteGroup = $groupObjectNotification->getOnDeleteGroup()) {
            $groups[] = $onDeleteGroup->getName();
            $url = $groupObjectNotification->getEntityDetail()->getObjectUrl();
            $objectDescription = $groupObjectNotification->getEntityDetail()->getObjectDescription();
        }

        $to = array();
        foreach ($userObjectNotifications as $userObjectNotification) {
            if ($userObjectNotification->getOnDelete()) {
                $to[$userObjectNotification->getUser()->getEmail()] = $userObjectNotification->getUser()->getFullName();
                $url = $userObjectNotification->getEntityDetail()->getObjectUrl();
                $objectDescription = $userObjectNotification->getEntityDetail()->getObjectDescription();
            }
        }

        foreach ($watchingUserNotifications as $watchingUserNotification) {
            if ($watchingUserNotification->getOnDelete()) {
                $to[$watchingUserNotification->getUser()->getEmail()] = $watchingUserNotification->getUser()->getFullName();
                $url = $watchingUserNotification->getEntityDetail()->getObjectUrl();
                $objectDescription = $watchingUserNotification->getEntityDetail()->getObjectDescription();
            }
        }

        if (!count($to) && !count($groups)) {
            return;
        }

        $annotationReader = new AnnotationReader();
        $reflectionClass = new \ReflectionClass(get_class($entity));
        $classAnnotations = $annotationReader->getClassAnnotations($reflectionClass);

        $from = array($this->mailerUser => $this->appName);
        $objectDescription = sprintf(
            '%s %s',
            $objectDescription,
            ($entity instanceof BaseRequest) ? $entity->getAlias() : $entity->getId()
        );

        $this->mailer->send(
            $objectDescription . ' Deleted',
            'CarbonApiBundle:objectNotification:delete.html.twig',
            $to,
            array(
                'deletingUser' => $deletingUser->getFullName(),
                'objectDescription' => $objectDescription,
            ),
            $from,
            $groups
        );

    }
}

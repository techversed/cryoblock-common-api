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
    public function __construct(CryoblockMailer $mailer, Logger $logger, $mailerUser, $tokenStorage, $frontendUrl)
    {
        $this->mailer = $mailer;
        $this->logger = $logger;
        $this->mailerUser = $mailerUser;
        $this->tokenStorage = $tokenStorage;
        $this->frontendUrl = $frontendUrl;
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        return; //need to get the detail id posting correctly...
        $entity = $args->getEntity(); // This will need to be changed...
        $em = $args->getEntityManager();
        $uow = $em->getUnitOfWork();

        if (!$this->tokenStorage->getToken()) {
            return;
        }

        $creatingUser = $this->tokenStorage->getToken()->getUser();

        // set creating user to watch update and complete

        //We will need to change what we are finding by.
        //WE ARE JUST GOING TO WORRY ABOU THIS LATER... JUST GET USER OBJECT NOTIFICATIONS WORKING FOR THE TIME BEING....
        // $groupObjectNotification = $em->getRepository('Carbon\ApiBundle\Entity\GroupObjectNotification') //Have not even changed this yet...
        //     ->findOneBy(array(
        //         'entity' => get_class($entity)
        //     ))
        // ;

        //We will need to change what we  are finding by.
        // $em->getRepository('Carbon\ApiBundle\EntityDetail')->findOneBy(
        //     )
        $userObjectNotifications = $em->getRepository('Carbon\ApiBundle\Entity\UserObjectNotification')
            ->findBy(array(
                'entityDetailId' => 1, //get_class($entity), // This will need to be changed.
                'entityId' => null
            ))
        ;

        //WE ARE GOING TO GET AUTOWATCHING WORKING AFTER THE MAJOR REFACTORING THAT WE NEED TO DO.... FOR THE TIME BEING LET'S JUST COMMENT THIS OUT.
        // print_r(get_object_vars($entity));
        // $creatingUserObjectNotification = new UserObjectNotification();

        //$creatingUserObjectNotification->setEntity($entity); //This should be where it was bombing...

        // $creatingUserObjectNotification->setUser($creatingUser);
        // $creatingUserObjectNotification->setUrl($this->frontendUrl);                        // This will need to be changed.... since we are moving towards storing this in the entity detail table...
        // $creatingUserObjectNotification->setOnUpdate(true);
        // $creatingUserObjectNotification->setOnDelete(true);
        // $em->persist($creatingUserObjectNotification);
        // $em->flush();
        //END OF CHANGES FOR AUTOWATCHING

        //COMMENTED OUT THE GROUPS SECTION
        // $groups = array();
        // if ($groupObjectNotification && $onCreateGroup = $groupObjectNotification->getOnCreateGroup()) {
        //     $groups[] = $onCreateGroup->getName();
        //     $url = $groupObjectNotification->getUrl();
        //     $objectDescription = $groupObjectNotification->getObjectDescription();
        // }
        //END OF GROUPS SECTION

        $to = array();
        foreach ($userObjectNotifications as $userObjectNotification) {
            if ($userObjectNotification->getOnCreate()) {
                $to[$userObjectNotification->getUser()->getEmail()] = $userObjectNotification->getUser()->getFullName();
                $url = $userObjectNotification->getUrl();
                $objectDescription = $userObjectNotification->getObjectDescription();
            }
        }

        //commented out the grousp section while we are getting the users working...
        if (!count($to) /*&& !count($groups)*/) {
            return;
        }

        $annotationReader = new AnnotationReader();
        $reflectionClass = new \ReflectionClass(get_class($entity)); //This will still create problems.
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

        $from = array($this->mailerUser => 'Crowelab Utilities');
        $objectDescription = sprintf(
            '%s %s',
            $objectDescription,
            ($entity instanceof BaseRequest) ? $entity->getAlias() : $entity->getId()
        );

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
            $from/*,
            $groups*/ // commented out in order to get the regular mailer working first.
        );
    }

    public function postUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $em = $args->getEntityManager();
        $uow = $em->getUnitOfWork();

        if (!$this->tokenStorage->getToken()) {
            return;
        }

        $updatingUser = $this->tokenStorage->getToken()->getUser();

        //We will need to change what we are finding by.
        $groupObjectNotification = $em->getRepository('Carbon\ApiBundle\Entity\GroupObjectNotification')
            ->findOneBy(array(
                'entity' => get_class($entity)
            ))
        ;

        //We will need to change what we are finding by.
        $userObjectNotifications = $em->getRepository('Carbon\ApiBundle\Entity\UserObjectNotification')
            ->findBy(array(
                'entity' => get_class($entity),
                'entityId' => null
            ))
        ;

        //We will need to change what we are finding by.
        $watchingUserNotifications = $em->getRepository('Carbon\ApiBundle\Entity\UserObjectNotification')
            ->findBy(array(
                'entity' => get_class($entity),
                'entityId' => $entity->getId(),
            ))
        ;

        $groups = array();
        if ($groupObjectNotification && $onUpdateGroup = $groupObjectNotification->getOnUpdateGroup()) {
            $groups[] = $onUpdateGroup->getName();
            $url = $groupObjectNotification->getUrl();
            $objectDescription = $groupObjectNotification->getObjectDescription();
        }

        $to = array();
        foreach ($userObjectNotifications as $userObjectNotification) {
            if ($userObjectNotification->getOnUpdate()) {
                $to[$userObjectNotification->getUser()->getEmail()] = $userObjectNotification->getUser()->getFullName();
                $url = $userObjectNotification->getUrl();
                $objectDescription = $userObjectNotification->getObjectDescription();
            }
        }

        foreach ($watchingUserNotifications as $watchingUserNotification) {
            if ($watchingUserNotification->getOnUpdate()) {
                $to[$watchingUserNotification->getUser()->getEmail()] = $watchingUserNotification->getUser()->getFullName();
                $url = $watchingUserNotification->getUrl();
                $objectDescription = $watchingUserNotification->getObjectDescription();
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

        $from = array($this->mailerUser => 'Crowelab Utilities');
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
            $from,
            $groups
        );
    }

    public function postSoftDelete(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $em = $args->getEntityManager();
        $uow = $em->getUnitOfWork();

        if (!$this->tokenStorage->getToken()) {
            return;
        }

        $deletingUser = $this->tokenStorage->getToken()->getUser();

        //We will need to change what we are finding by.
        $groupObjectNotification = $em->getRepository('Carbon\ApiBundle\Entity\GroupObjectNotification')
            ->findOneBy(array(
                'entity' => get_class($entity)
            ))
        ;

        //We will need to change what we are finding by.
        $userObjectNotifications = $em->getRepository('Carbon\ApiBundle\Entity\UserObjectNotification')
            ->findBy(array(
                'entity' => get_class($entity),
                'entityId' => null
            ))
        ;

        //We will need to change what we are finding by.
        $watchingUserNotifications = $em->getRepository('Carbon\ApiBundle\Entity\UserObjectNotification')
            ->findBy(array(
                'entity' => get_class($entity),
                'entityId' => $entity->getId(),
            ))
        ;

        $groups = array();
        if ($groupObjectNotification && $onDeleteGroup = $groupObjectNotification->getOnDeleteGroup()) {
            $groups[] = $onDeleteGroup->getName();
            $url = $groupObjectNotification->getUrl();
            $objectDescription = $groupObjectNotification->getObjectDescription();
        }

        $to = array();
        foreach ($userObjectNotifications as $userObjectNotification) {
            if ($userObjectNotification->getOnDelete()) {
                $to[$userObjectNotification->getUser()->getEmail()] = $userObjectNotification->getUser()->getFullName();
                $url = $userObjectNotification->getUrl();
                $objectDescription = $userObjectNotification->getObjectDescription();
            }
        }

        foreach ($watchingUserNotifications as $watchingUserNotification) {
            if ($watchingUserNotification->getOnDelete()) {
                $to[$watchingUserNotification->getUser()->getEmail()] = $watchingUserNotification->getUser()->getFullName();
                $url = $watchingUserNotification->getUrl();
                $objectDescription = $watchingUserNotification->getObjectDescription();
            }
        }

        if (!count($to) && !count($groups)) {
            return;
        }

        $annotationReader = new AnnotationReader();
        $reflectionClass = new \ReflectionClass(get_class($entity));
        $classAnnotations = $annotationReader->getClassAnnotations($reflectionClass);

        $from = array($this->mailerUser => 'Crowelab Utilities');
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

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
        $entity = $args->getEntity(); // This will need to be changed...
        $em = $args->getEntityManager();
        $uow = $em->getUnitOfWork();
        // $url = "temporary thingy";
        // $objectDescription = "here is a temporary description";

        //If the object that is being created is a notification object don't send notifications or set anyone as a listener for the object.
        if (get_class($entity) == 'Carbon\ApiBundle\Entity\UserObjectNotification') {
            return;
        }

        if (!$this->tokenStorage->getToken()) {
            return;
        }

        $creatingUser = $this->tokenStorage->getToken()->getUser();

        $entDetId = $em->getRepository('Carbon\ApiBundle\Entity\EntityDetail')->findOneBy(array(
            'objectClassName' => get_class($entity)
        ));
        if ($entDetId != null) {
            $entDetId = $entDetId->getId();
        }
        else return;
        //otherwise we should probably return...

        //print_r($entDetId);


        //check return value and create a new entry if it is null...
        //if not null get the id...

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
                'entityDetailId' => $entDetId, //get_class($entity), // This will need to be changed.
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
                $url = $userObjectNotification->getEntityDetail()->getObjectUrl(); // Removed in the rework may want to insert it into the table.
                $objectDescription = $userObjectNotification->getEntityDetail()->getObjectDescription(); //Removed when we switched to the entity_detail table method.
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

    //The request will still die if we make it into this function because it still uses entity which has been discontinued...
    public function postUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $em = $args->getEntityManager();
        $uow = $em->getUnitOfWork();

        //need to check to make sure that it is not a userobject notification object... or else we will end up spamming people whenever they change permissions.
        if (get_class($entity) == 'Carbon\ApiBundle\Entity\UserObjectNotification') {
            return;
        }

        if (!$this->tokenStorage->getToken()) {
            return;
        }

        $updatingUser = $this->tokenStorage->getToken()->getUser();

        //Look up the entity detail id for the entry that we are working with.


        // die;

        $entDetId = $em->getRepository('Carbon\ApiBundle\Entity\EntityDetail')->findOneBy(array(
            'objectClassName' => get_class($entity)
        ))->getId();

        // $entDetId = $em->getRepository('Carbon\ApiBundle\Entity\EntityDetail')->findOneBy(array(
        //     'objectClassName' => get_class($entity)
        // ));
        // print_r($entDetId);
        // die();
        // print_r($entDetId);
        // die;
        // $entDetId = 9;

        // //Worry about groupObject nofitications last...
        // $groupObjectNotification = $em->getRepository('Carbon\ApiBundle\Entity\GroupObjectNotification')
        //     ->findOneBy(array(
        //         'entity' => get_class($entity)
        //     ))
        // ;

        //We will need to change what we are finding by.
        $userObjectNotifications = $em->getRepository('Carbon\ApiBundle\Entity\UserObjectNotification')
            ->findBy(array(
                // 'entity' => get_class($entity), // removed when we switched to having the entity detail page.
                'entityDetailId' => $entDetId,
                'entityId' => null
            ))
        ;

        //We will need to change what we are finding by.
        $watchingUserNotifications = $em->getRepository('Carbon\ApiBundle\Entity\UserObjectNotification')
            ->findBy(array(
                // 'entity' => get_class($entity), // removed when we switched to having the entity detail page
                'entityDetailId' => $entDetId,
                'entityId' => $entity->getId(),
            ))
        ;

        // $groups = array();
        // if ($groupObjectNotification && $onUpdateGroup = $groupObjectNotification->getOnUpdateGroup()) {
        //     $groups[] = $onUpdateGroup->getName();
        //     $url = $groupObjectNotification->getUrl();
        //     $objectDescription = $groupObjectNotification->getObjectDescription();
        // }

        $to = array();
        foreach ($userObjectNotifications as $userObjectNotification) {
            if ($userObjectNotification->getOnUpdate()) {
                $to[$userObjectNotification->getUser()->getEmail()] = $userObjectNotification->getUser()->getFullName();
                // $url = $userObjectNotification->getUrl(); // We are removing the url from this location....
                // $objectDescription = $userObjectNotification->getObjectDescription(); // We are removing the description from thsi
            }
        }

        foreach ($watchingUserNotifications as $watchingUserNotification) {
            if ($watchingUserNotification->getOnUpdate()) {
                $to[$watchingUserNotification->getUser()->getEmail()] = $watchingUserNotification->getUser()->getFullName();
                // $url = $watchingUserNotification->getUrl(); // We are removing the url and descripiton form this
                // $objectDescription = $watchingUserNotification->getObjectDescription(); //we are removing the description from this
            }
        }

        //uncomment this when it is time to get group notifications working again.
        if (!count($to) /*&& !count($groups)*/) {
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
            $from
            /*,
            $groups
            */
        );
        //uncomment groups once it is time to get things working again...
    }

    //implement soft delete last....
    //The request will still die if we make it into this function because we have removed the entity attritube from the UserObjectNotification class and are slowly making changes to make all this work out in the end.
    public function postSoftDelete(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $em = $args->getEntityManager();
        $uow = $em->getUnitOfWork();

        if (!$this->tokenStorage->getToken()) {
            return;
        }

        //If the object that is being handled is a user object notification object don't do anything to it.
        if (get_class($entity) == 'Carbon\ApiBundle\Entity\UserObjectNotification') {
            return;
        }

        $deletingUser = $this->tokenStorage->getToken()->getUser();

        //Will need to change this going forwards....
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

        //Get users working first ... worry about this later...
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

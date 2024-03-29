<?php

namespace Carbon\ApiBundle\Listener;

use Carbon\ApiBundle\Entity\Production\BaseRequest;
use Carbon\ApiBundle\Service\CryoblockMailer;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use Doctrine\ORM\Event\PostFlushEventArgs;

// Classes where we are not calling this listener
use Carbon\ApiBundle\Entity\EntityDetail;
use Carbon\ApiBundle\Entity\UserObjectNotification;
use Carbon\ApiBundle\Entity\Production\BaseRequestProjectInterface;
use Carbon\ApiBundle\Entity\Production\BaseRequestSampleInterface;
use Carbon\ApiBundle\Entity\Project\BaseProjectSample;
use Carbon\ApiBundle\Entity\Comment;
use Gedmo\Loggable\Entity\LogEntry;
use Carbon\ApiBundle\Entity\Storage\BaseDivision;
use Carbon\ApiBundle\Entity\Storage\BaseAccessGovernor;

use AppBundle\Entity\Record\VimSample; // Things which are specific to the Crowelab implemenation should not be mentioned in common


/*

    This listener sends emails to notifiy users when they objects and types of objects that they watch are updated.

    There are 2 types of notifications
        User object notifications and user object notificatiosn
        Group object notifications specifies the groups that should be notified with every update and creation.


    Entity detail also plays a heavy role in the operation of this file. Essentially an EntityDetail entry is a collection of metadata that goes along with each type of entity that defines how the Entity interacts with the various other services that are built into the system.
        The autowatch feature for entity deatil makes it so that the user who creates an entity will automatically watch the entity that is created.


    Upcoming improvements:
        The url and description is being set with every iteration of userobjectnoficiation and group objectnofitication
        Need to check how the !entDet instanceof EntityDetail is defiend.


    Long term suggested changes:
        We might want to move the group filtering that takes place into this file and possibly kill support for sending to groups altogether.
        It might make sense to keep it simple and just have users in cryoblock mailer.
*/

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

    // If the entity in question extends one of the following classes or implementes one of the following interfaces it should be ignored.
    public function classOrInterfaceIgnored($entity){

        if(
            ($entity instanceof BaseRequestProjectInterface) ||
            ($entity instanceof BaseRequestSampleInterface) ||
            ($entity instanceof EntityDetail) ||
            ($entity instanceof BaseProjectSample) ||
            ($entity instanceof LogEntry) ||
            ($entity instanceof Comment) ||
            ($entity instanceof BaseDivision) ||
            ($entity instanceof UserObjectNotification) ||
            ($entity instanceof GroupObjectNotification) ||
            ($entity instanceof VimSample) ||
            ($entity instanceof BaseAccessGovernor)
        ){
            return true;
        }

        return false;
    }

    private $needsFlush = false;

    public function postPersist(LifecycleEventArgs $args)
    {

        $em = $args->getEntityManager();
        $uow = $em->getUnitOfWork();

        $entity = $args->getEntity();

        if ($this->classOrInterfaceIgnored($entity)) {
            return;
        }

        if (!$this->tokenStorage->getToken()) {
            return;
        }

        $creatingUser = $this->tokenStorage->getToken()->getUser();
        $entDet = $em->getRepository('Carbon\ApiBundle\Entity\EntityDetail')->findOneBy(array('objectClassName' => get_class($entity)));

        if (!$entDet instanceof EntityDetail) {
            return;
        }

        $entDetId = $entDet->getId();

        if($entDet->getAutoWatch() == true) {
            $creatingUserObjectNotification = new UserObjectNotification();
            $creatingUserObjectNotification->setEntityId($entity->getId());
            $creatingUserObjectNotification->setLinkedEntityDetail($entDet);

            // 2021-06-24 SD - Sloppy fix but w/e. For new Antibody Requests it needs to be watched by the old Protein Expression Creator
            if($entDetId == 7) {
                $creatingUserObjectNotification->setUser($entity->getCreatedBy());
            } else {
                $creatingUserObjectNotification->setUser($creatingUser);
            }
            $creatingUserObjectNotification->setOnUpdate(true);
            $creatingUserObjectNotification->setOnDelete(true);
            $em->persist($creatingUserObjectNotification);
            $this->needsFlush = true;
            // $em->flush();

        }

        $groupObjectNotification = $em->getRepository('Carbon\ApiBundle\Entity\GroupObjectNotification')
            ->findOneBy(array(
                'linkedEntityDetailId' => $entDetId
            ))
        ;

        $userObjectNotifications = $em->getRepository('Carbon\ApiBundle\Entity\UserObjectNotification')
            ->findBy(array(
                'linkedEntityDetailId' => $entDetId,
                'entityId' => null
            ))
        ;

        $groups = array();
        if ($groupObjectNotification && $onCreateGroup = $groupObjectNotification->getOnCreateGroup()) {
            $groups[] = $onCreateGroup->getName();
            $url = $groupObjectNotification->getLinkedEntityDetail()->getObjectUrl();
            $objectDescription = $groupObjectNotification->getLinkedEntityDetail()->getObjectDescription();
        }

        $to = array();
        foreach ($userObjectNotifications as $userObjectNotification) {
            if ($userObjectNotification->getOnCreate() && $userObjectNotification->getUser() != $creatingUser) {

                if (is_object($userObjectNotification->getUser())) {
                    if ($userObjectNotification->getUser()->isEnabled()) {
                        $to[$userObjectNotification->getUser()->getEmail()] = $userObjectNotification->getUser()->getFullName();
                    }
                }

                $url = $userObjectNotification->getLinkedEntityDetail()->getObjectUrl();
                $objectDescription = $userObjectNotification->getLinkedEntityDetail()->getObjectDescription();
            }
        }

        if (!count($to) && !count($groups)) {
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

        if ($this->classOrInterfaceIgnored($entity)) {
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
                'linkedEntityDetailId' => $entDetId
            ))
        ;

        $userObjectNotifications = $em->getRepository('Carbon\ApiBundle\Entity\UserObjectNotification')
            ->findBy(array(
                'linkedEntityDetailId' => $entDetId,
                'entityId' => null
            ))
        ;

        $watchingUserNotifications = $em->getRepository('Carbon\ApiBundle\Entity\UserObjectNotification')
            ->findBy(array(
                'linkedEntityDetailId' => $entDetId,
                'entityId' => $entity->getId(),
            ))
        ;

        $groups = array();
        if ($groupObjectNotification && $onUpdateGroup = $groupObjectNotification->getOnUpdateGroup()) {
            $groups[] = $onUpdateGroup->getName();
            $url = $groupObjectNotification->getLinkedEntityDetail()->getObjectUrl();
            $objectDescription = $groupObjectNotification->getLinkedEntityDetail()->getObjectDescription();
        }

        $to = array();
        foreach ($userObjectNotifications as $userObjectNotification) {
            if ($userObjectNotification->getOnUpdate()) {

                if (is_object($userObjectNotification->getUser())) {
                    if ($userObjectNotification->getUser()->isEnabled() == true){
                        $to[$userObjectNotification->getUser()->getEmail()] = $userObjectNotification->getUser()->getFullName();
                    }
                }

                $url = $userObjectNotification->getLinkedEntityDetail()->getObjectUrl();
                $objectDescription = $userObjectNotification->getLinkedEntityDetail()->getObjectDescription();

            }
        }

        foreach ($watchingUserNotifications as $watchingUserNotification) {
            if ($watchingUserNotification->getOnUpdate()) {

                if (is_object($watchingUserNotification->getUser())) {
                    if ($watchingUserNotification->getUser()->isEnabled() == true) {
                        $to[$watchingUserNotification->getUser()->getEmail()] = $watchingUserNotification->getUser()->getFullName();
                    }
                }

                $url = $watchingUserNotification->getLinkedEntityDetail()->getObjectUrl();
                $objectDescription = $watchingUserNotification->getLinkedEntityDetail()->getObjectDescription();

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

        if ($this->classOrInterfaceIgnored($entity)) {
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
                'linkedEntityDetailId' => $entDetId
            ))
        ;

        $userObjectNotifications = $em->getRepository('Carbon\ApiBundle\Entity\UserObjectNotification')
            ->findBy(array(
                'linkedEntityDetailId' => $entDetId,
                'entityId' => null
            ))
        ;

        $watchingUserNotifications = $em->getRepository('Carbon\ApiBundle\Entity\UserObjectNotification')
            ->findBy(array(
                'linkedEntityDetailId' => $entDetId,
                'entityId' => $entity->getId(),
            ))
        ;

        $groups = array();
        if ($groupObjectNotification && $onDeleteGroup = $groupObjectNotification->getOnDeleteGroup()) {
            $groups[] = $onDeleteGroup->getName();
            $url = $groupObjectNotification->getLinkedEntityDetail()->getObjectUrl();
            $objectDescription = $groupObjectNotification->getLinkedEntityDetail()->getObjectDescription();
        }

        $to = array();
        foreach ($userObjectNotifications as $userObjectNotification) {
            if ($userObjectNotification->getOnDelete()) {

                if (is_object($userObjectNotification->getUser())) {
                    if ($userObjectNotification->getUser()->isEnabled() == true){
                        $to[$userObjectNotification->getUser()->getEmail()] = $userObjectNotification->getUser()->getFullName();
                    }
                }

                $url = $userObjectNotification->getLinkedEntityDetail()->getObjectUrl();
                $objectDescription = $userObjectNotification->getLinkedEntityDetail()->getObjectDescription();
            }
        }

        // Check to make sure that the user in question is still enabled
        foreach ($watchingUserNotifications as $watchingUserNotification) {
            if ($watchingUserNotification->getOnDelete()) {

                if (is_object($watchingUserNotification->getUser())) {
                    if($watchingUserNotification->getUser()->isEnabled() == true){
                        $to[$watchingUserNotification->getUser()->getEmail()] = $watchingUserNotification->getUser()->getFullName();
                    }
                }

                // This does not need to be set with every iteration of the loop
                $url = $watchingUserNotification->getLinkedEntityDetail()->getObjectUrl();
                $objectDescription = $watchingUserNotification->getLinkedEntityDetail()->getObjectDescription();
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

    public function postFlush(PostFlushEventArgs $args) {

        $em = $args->getEntityManager();

        if ($this->needsFlush == true){
            $this->needsFlush = false;
            $em->flush();
        }

    }

}

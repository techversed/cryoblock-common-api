<?php

namespace Carbon\ApiBundle\Listener\Storage;

use AppBundle\Entity\Storage\Division;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Symfony\Bridge\Monolog\Logger;

use AppBundle\Entity\Storage\DivisionEditor;
use AppBundle\Entity\Storage\DivisionViewer;
use AppBundle\Entity\Storage\DivisionGroupEditor;
use AppBundle\Entity\Storage\DivisionGroupViewer;
use AppBundle\Entity\Storage\DivisionStorageContainer;
use AppBundle\Entity\Storage\DivisionSampleType;

/*
Base Division Listener
Written by Andre Jon Branchizio
Heavily Modified by Taylor Jones

Originally we chose to make it so that the permissions of chidren divisions would be overwritten by changes to their immediate parent
This has been modified so that the changes that take place with the parent will also be applied to the children

Example:
Setup:
    Division 1 has 5 children say (2,3,4,5)
    Division 1 permissions editable by andre, allows hybridoma, allows vials
    Division 2 Editable by andre, allows cell supernatant, allows eppendorf tubes
    Division 3 Editable by Dr crowe, allows protein, allows vials

Action:
    Add taylor to division


Old system outcome:

    Division 1 and all of its children are editable by andre + taylor, allow hybridoma, allows vials

New system outcome:
    Division 1 permissions editable by andre & taylor, allows hybridoma, allows vials
    Division 2 Editable by andre & taylor, allows cell supernatant, allows eppendorf tubes
    Division 3 Editable by Dr Crowe & taylor, allows protein, allows vials

Later versions may make it possible to select the method fo cascading which is going to be used for this update operation -- I think that this will present problems because users will not take the time to get to understand the tools that we are creating

*/

// The class name is the full path becasue because it was saying that I was trying to load from global namespace when I was first

class BaseDivisionListener
{
    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }

    public function addToChildren($em, $repo, $entity, $className)
    {
        // echo gettype($entity);

        // If the entity exists for a child class
        $test = new $className();


        return true;

    }

    public function removeFromChildren($em, $repo, $entity, $className)
    {

        // If work happened return true;
        return true;

    }

    public function updateDivisionBooleans()
    {
        // If work happened here return true
        return true;
    }

    //Could I flush the entity manager if there are in fact updates that need to take place... Should be able to use a conditional in order to avoid an infinite loop.
    public function onFlush(OnFlushEventArgs $args)
    {

        //I could make is to that you create new entities which for each division that exclusively have the divison changed to all children divisions of the one that we are concerend with
        //enitty->getDivision()->getChildren() -- insert a new entity which is just like the one that we are inserting but with more of t
        $em = $args->getEntityManager();
        $uow = $em->getUnitOfWork();
        $workHappened = false;

        // If we are dealing with a division then then you copy the booleans to the other division

            // If the unit of work is an insertion
        foreach ($uow->getScheduledEntityInsertions() as $keyEntity => $entity) {

            if ($entity instanceof Division) {

                continue;

            }

            if ($entity instanceof DivisionViewer) {

                $workHappend = $this->addToChildren($em, null, $entity, 'AppBundle\Entity\Storage\DivisionViewer') ? true : $workHappened;
                continue;

            }

            if ($entity instanceof DivisionEditor) {

                $workHappend = $this->addToChildren($em, null, $entity, 'AppBundle\Entity\Storage\DivisionEditor') ? true : $workHappened;
                continue;

            }

            if ($entity instanceof DivisionStorageContainer) {

                $workHappend = $this->addToChildren($em, null, $entity, 'AppBundle\Entity\Storage\DivisionStorageContainer') ? true : $workHappened;
                continue;

            }

            if ($entity instanceof DivisionSampleType) {

                $workHappend = $this->addToChildren($em, null, $entity, 'AppBundle\Entity\Storage\DivisionSampleType') ? true : $workHappened;
                continue;

            }

        }
        // If it is an insertion
            // If it is a Divison
                // Return

            // If it is a Division editor

            // If it is a Division Viewer

            // If it is a Division Group editor

            // If it is a Division Storage Container

            // If it is a Division Sample Type


        foreach ($uow->getScheduledEntityDeletions() as $keyEntity => $entity) {

            // We should not ever be deleting divisions imo (if we do delete them we should handle that by hand cause we don't want to give that kind of power to the users... They'll fuck it up.)
                // Certainly no cascade delete...
            if ($entity instanceof Division){

                continue;
            }

            if ($entity instanceof DivisionViewer) {

                $workHappend = $this->removeFromChildren($em, null, $entity, 'AppBundle\Entity\Storage\DivisionSampleType') ? true : $workHappened;
                continue;

            }

            if ($entity instanceof DivisionEditor) {

                $workHappend = $this->removeFromChildren($em, null, $entity, 'AppBundle\Entity\Storage\DivisionEditor') ? true : $workHappened;
                continue;

            }

            if ($entity instanceof DivisionStorageContainer) {

                $workHappend = $this->removeFromChildren($em, null, $entity, 'AppBundle\Entity\Storage\DivisionStorageContainer') ? true : $workHappened;
                continue;

            }

            if ($entity instanceof DivisionSampleType) {

                $workHappend = $this->removeFromChildren($em, null, $entity, 'AppBundle\Entity\Storage\DivisionSampleType') ? true : $workHappened;
                continue;

            }

        }

        // If it is a deletion
            // If it is a Division editor

            // If it is a Division Viewer

            // If it is a Division Group editor

            // If it is a Division Storage Container

            // If it is a Division Sample Type


        foreach ($uow->getScheduledEntityUpdates() as $keyEntity => $entity) {

            if ($entity instanceof Division){

                $workHappened = $this->updateDivisionBooleans() ? true : $workHappened;

            }

            //Don't think that we will ever need to update any other type of entity pertaning to storage

        }

        // If an update took place then we need to flush the entity manager;

        if($workHappened){
            $em->flush();
        }

        // $em = $args->getEntityManager();
        // $uow = $em->getUnitOfWork();
        // $parentDivision = null;
        // $isPublicEdit = null;
        // $isPublicView = null;
        // $allowAllStorageContainers = null;
        // $allowAllSampleTypes = null;

        // $divisionEditors = array();
        // $divisionGroupEditors = array();
        // $divisionViewers = array();
        // $divisionGroupViewers = array();
        // $divisionStorageContainers = array();
        // $divisionSampleTypes = array();

        // $removingDivisionEditors = array();
        // $removingDivisionGroupEditors = array();
        // $removingDivisionViewers = array();
        // $removingDivisionGroupViewers = array();
        // $removingDivisionStorageContainers = array();
        // $removingDivisionSampleTypes = array();

        // // die();

        // foreach ($uow->getScheduledEntityInsertions() as $keyEntity => $entity) {

        //     if ($entity instanceof DivisionEditor) {
        //         $division = $entity->getDivision();
        //         if (!$parentDivision) {
        //             $parentDivision = $division;
        //             $divisionEditors[] = $entity->getUser();
        //         }
        //     }

        //     if ($entity instanceof DivisionViewer) {
        //         $division = $entity->getDivision();
        //         if (!$parentDivision) {
        //             $parentDivision = $division;
        //             $divisionViewers[] = $entity->getUser();
        //         }
        //     }

        //     if ($entity instanceof DivisionGroupEditor) {
        //         $division = $entity->getDivision();
        //         if (!$parentDivision) {
        //             $parentDivision = $division;
        //             $divisionGroupEditors[] = $entity->getGroup();
        //         }
        //     }

        //     if ($entity instanceof DivisionGroupViewer) {
        //         $division = $entity->getDivision();
        //         if (!$parentDivision) {
        //             $parentDivision = $division;
        //             $divisionGroupViewers[] = $entity->getGroup();
        //         }
        //     }

        //     if ($entity instanceof DivisionStorageContainer) {
        //         $division = $entity->getDivision();
        //         if (!$parentDivision) {
        //             $parentDivision = $division;
        //             $divisionStorageContainers[] = $entity->getStorageContainer();
        //         }
        //     }

        //     if ($entity instanceof DivisionSampleType) {
        //         $division = $entity->getDivision();
        //         if (!$parentDivision) {
        //             $parentDivision = $division;
        //             $divisionSampleTypes[] = $entity->getSampleType();
        //         }
        //     }

        // }

        // // handle is is public edit or is public view and allowAllStorageContainers and allowAllSampleTypes
        // foreach ($uow->getScheduledEntityUpdates() as $keyEntity => $entity) {

        //     if ($entity instanceof Division) {

        //         foreach ($uow->getEntityChangeSet($entity) as $keyField => $field) {

        //             if ($keyField === 'isPublicEdit') {
        //                 if (!$parentDivision) {
        //                     $parentDivision = $entity;
        //                 }
        //                 $isPublicEdit = $field[1];
        //             }

        //             if ($keyField === 'isPublicView') {
        //                 if (!$parentDivision) {
        //                     $parentDivision = $entity;
        //                 }
        //                 $isPublicView = $field[1];
        //             }

        //             if ($keyField === 'allowAllStorageContainers') {
        //                 if (!$parentDivision) {
        //                     $parentDivision = $entity;
        //                 }
        //                 $allowAllStorageContainers = $field[1];
        //             }

        //             if ($keyField === 'allowAllSampleTypes') {
        //                 if (!$parentDivision) {
        //                     $parentDivision = $entity;
        //                 }
        //                 $allowAllSampleTypes = $field[1];
        //             }

        //         }

        //     }

        // }

        // foreach ($uow->getScheduledEntityDeletions() as $keyEntity => $entity) {

        //     if ($entity instanceof DivisionEditor) {
        //         $division = $entity->getDivision();
        //         $user = $entity->getUser();
        //         if (!$parentDivision) {
        //             $parentDivision = $division;
        //             $removingDivisionEditors[$user->getId()] = $user;
        //         }
        //     }

        //     if ($entity instanceof DivisionViewer) {
        //         $division = $entity->getDivision();
        //         $user = $entity->getUser();
        //         if (!$parentDivision) {
        //             $parentDivision = $division;
        //             $removingDivisionViewers[$user->getId()] = $user;
        //         }
        //     }

        //     if ($entity instanceof DivisionGroupEditor) {
        //         $division = $entity->getDivision();
        //         $group = $entity->getGroup();
        //         if (!$parentDivision) {
        //             $parentDivision = $division;
        //             $removingDivisionGroupEditors[$group->getId()] = $group;
        //         }
        //     }

        //     if ($entity instanceof DivisionGroupViewer) {
        //         $division = $entity->getDivision();
        //         $group = $entity->getGroup();
        //         if (!$parentDivision) {
        //             $parentDivision = $division;
        //             $removingDivisionGroupViewers[$group->getId()] = $group;
        //         }
        //     }

        //     if ($entity instanceof DivisionStorageContainer) {
        //         $division = $entity->getDivision();
        //         $storageContainer = $entity->getStorageContainer();
        //         if (!$parentDivision) {
        //             $parentDivision = $division;
        //             $removingDivisionStorageContainers[$storageContainer->getId()] = $storageContainer;
        //         }
        //     }

        //     if ($entity instanceof DivisionSampleType) {
        //         $division = $entity->getDivision();
        //         $sampleType = $entity->getSampleType();
        //         if (!$parentDivision) {
        //             $parentDivision = $division;
        //             $removingDivisionSampleTypes[$sampleType->getId()] = $sampleType;
        //         }
        //     }

        // }

        // if (!$parentDivision) {
        //     return;
        // }

        // $currentDivisionEditors = $parentDivision->getDivisionEditors();
        // $currentDivisionGroupEditors = $parentDivision->getDivisionGroupEditors();
        // $currentDivisionViewers = $parentDivision->getDivisionViewers();
        // $currentDivisionGroupViewers = $parentDivision->getDivisionGroupViewers();
        // $currentDivisionStorageContainers = $parentDivision->getDivisionStorageContainers();
        // $currentDivisionSampleTypes = $parentDivision->getDivisionSampleTypes();

        // if ($isPublicView == null) {
        //     $isPublicView = $parentDivision->getIsPublicView();
        // }

        // if ($isPublicEdit == null) {
        //     $isPublicEdit = $parentDivision->getIsPublicEdit();
        // }
        // if ($allowAllStorageContainers == null) {
        //     $allowAllStorageContainers = $parentDivision->getAllowAllStorageContainers();
        // }
        // if ($allowAllSampleTypes == null) {
        //     $allowAllSampleTypes = $parentDivision->getAllowAllSampleTypes();
        // }

        // if ($currentDivisionEditors) {

        //     foreach ($currentDivisionEditors as $currentDivisionEditor) {
        //         if (!isset($removingDivisionEditors[$currentDivisionEditor->getUser()->getId()])) {
        //             $divisionEditors[] = $currentDivisionEditor->getUser();
        //         }
        //     }

        // }

        // if ($currentDivisionGroupEditors) {

        //     foreach ($currentDivisionGroupEditors as $currentDivisionGroupEditor) {
        //         if (!isset($removingDivisionGroupEditors[$currentDivisionGroupEditor->getGroup()->getId()])) {
        //             $divisionGroupEditors[] = $currentDivisionGroupEditor->getGroup();
        //         }
        //     }

        // }

        // if ($currentDivisionViewers) {

        //     foreach ($currentDivisionViewers as $currentDivisionViewer) {
        //         if (!isset($removingDivisionViewers[$currentDivisionViewer->getUser()->getId()])) {
        //             $divisionViewers[] = $currentDivisionViewer->getUser();
        //         }
        //     }

        // }

        // if ($currentDivisionGroupViewers) {

        //     foreach ($currentDivisionGroupViewers as $currentDivisionGroupViewer) {
        //         if (!isset($removingDivisionGroupViewers[$currentDivisionGroupViewer->getGroup()->getId()])) {
        //             $divisionGroupViewers[] = $currentDivisionGroupViewer->getGroup();
        //         }
        //     }

        // }

        // if (!$parentDivision->getChildren()) {

        //     return;

        // }

        // foreach ($currentDivisionStorageContainers as $currentDivisionStorageContainer) {
        //     if (!isset($removingDivisionStorageContainers[$currentDivisionStorageContainer->getStorageContainer()->getId()])) {
        //         $divisionStorageContainers[] = $currentDivisionStorageContainer->getStorageContainer();
        //     }
        // }

        // foreach ($currentDivisionSampleTypes as $currentDivisionSampleType) {
        //     if (!isset($removingDivisionSampleTypes[$currentDivisionSampleType->getSampleType()->getId()])){
        //         $divisionSampleTypes[] = $currentDivisionSampleType->getSampleType();
        //     }
        // }



        // //We might honestly be able to fix this just by making changes to this section.
        // foreach ($parentDivision->getChildren() as $child) {

        //     $newEditors = array();
        //     $newGroupEditors = array();
        //     $newViewers = array();
        //     $newGroupViewers = array();
        //     $newStorageContainers = array();
        //     $newSampleTypes = array();

        //     foreach ($child->getDivisionEditors() as $childEditor) {
        //         $uow->remove($childEditor);
        //     }

        //     foreach ($child->getDivisionGroupEditors() as $childGroupEditor) {
        //         $uow->remove($childGroupEditor);
        //     }

        //     foreach ($child->getDivisionViewers() as $childViewer) {
        //         $uow->remove($childViewer);
        //     }

        //     foreach ($child->getDivisionGroupViewers() as $childGroupViewer) {
        //         $uow->remove($childGroupViewer);
        //     }

        //     foreach ($child->getDivisionStorageContainers() as $childStorageContainer){
        //         $uow->remove($childStorageContainer);
        //     }

        //     foreach ($child->getDivisionSampleTypes() as $childSampleType){
        //         $uow->remove($childSampleType);
        //     }

        //     foreach ($divisionEditors as $divisionEditor) {

        //         $newEditor = new DivisionEditor();
        //         $newEditor->setDivision($child);
        //         $newEditor->setUser($divisionEditor);

        //         $uow->persist($newEditor);
        //         $metaEditor = $em->getClassMetadata(get_class($newEditor));
        //         $uow->computeChangeSet($metaEditor, $newEditor);

        //         $newEditors[] = $newEditor;
        //     }

        //     foreach ($divisionGroupEditors as $divisionGroupEditor) {

        //         $newGroupEditor = new DivisionGroupEditor();
        //         $newGroupEditor->setDivision($child);
        //         $newGroupEditor->setGroup($divisionGroupEditor);

        //         $uow->persist($newGroupEditor);
        //         $metaGroupEditor = $em->getClassMetadata(get_class($newGroupEditor));
        //         $uow->computeChangeSet($metaGroupEditor, $newGroupEditor);

        //         $newGroupEditors[] = $newGroupEditor;
        //     }

        //     foreach ($divisionViewers as $divisionViewer) {

        //         $newViewer = new DivisionViewer();
        //         $newViewer->setDivision($child);
        //         $newViewer->setUser($divisionViewer);

        //         $uow->persist($newViewer);
        //         $metaViewer = $em->getClassMetadata(get_class($newViewer));
        //         $uow->computeChangeSet($metaViewer, $newViewer);

        //         $newViewers[] = $newViewer;
        //     }

        //     foreach ($divisionGroupViewers as $divisionGroupViewer) {

        //         $newGroupViewer = new DivisionGroupViewer();
        //         $newGroupViewer->setDivision($child);
        //         $newGroupViewer->setGroup($divisionGroupViewer);

        //         $uow->persist($newGroupViewer);
        //         $metaGroupViewer = $em->getClassMetadata(get_class($newGroupViewer));
        //         $uow->computeChangeSet($metaGroupViewer, $newGroupViewer);

        //         $newGroupViewers[] = $newGroupViewer;
        //     }

        //     foreach ($divisionStorageContainers as $divisionStorageContainer) {
        //         $newStorageContainer = new DivisionStorageContainer();
        //         $newStorageContainer->setDivision($child);
        //         $newStorageContainer->setStorageContainer($divisionStorageContainer);

        //         $uow->persist($newStorageContainer);
        //         $metaStorageContainer = $em->getClassMetadata(get_class($newStorageContainer));
        //         $uow->computeChangeSet($metaStorageContainer, $newStorageContainer);

        //         $newStorageContainers[] = $newStorageContainer;
        //     }

        //     foreach ($divisionSampleTypes as $divisionSampleType) {
        //         $newSampleType = new DivisionSampleType();
        //         $newSampleType->setDivision($child);
        //         $newSampleType->setSampleType($divisionSampleType);

        //         $uow->persist($newSampleType);
        //         $metaSampleType = $em->getClassMetadata(get_class($newSampleType));
        //         $uow->computeChangeSet($metaSampleType, $newSampleType);

        //         $newSampleTypes[] = $newSampleType;
        //     }

        //     $child->setIsPublicEdit($isPublicEdit); //Should we make it so that there are strict conditions for when to overwrite the permissions
        //     $child->setIsPublicView($isPublicView); // Should we make it so that this only changes if there is an update to the
        //     $child->setAllowAllSampleTypes($allowAllSampleTypes);
        //     $child->setAllowAllStorageContainers($allowAllStorageContainers);

        //     $child->setDivisionEditors($newEditors);
        //     $child->setDivisionGroupEditors($newGroupEditors);
        //     $child->setDivisionViewers($newViewers);
        //     $child->setDivisionGroupViewers($newGroupViewers);
        //     $child->setDivisionStorageContainers($newStorageContainers);
        //     $child->setDivisionSampleTypes($newSampleTypes);

        //     $metaDivision = $em->getClassMetadata(get_class($child));
        //     $uow->computeChangeSet($metaDivision, $child);
        // }
    }
}

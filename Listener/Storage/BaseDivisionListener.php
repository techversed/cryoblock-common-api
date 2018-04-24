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

class BaseDivisionListener
{
    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }

    public function onFlush(OnFlushEventArgs $args)
    {
        $em = $args->getEntityManager();
        $uow = $em->getUnitOfWork();
        $parentDivision = null;
        $isPublicEdit = null;
        $isPublicView = null;
        $allowAllStorageContainers = null;
        $allowAllSampleTypes = null;


        $divisionEditors = array();
        $divisionGroupEditors = array();
        $divisionViewers = array();
        $divisionGroupViewers = array();
        $divisionStorageContainers = array();
        $divisionSampleTypes = array();

        $removingDivisionEditors = array();
        $removingDivisionGroupEditors = array();
        $removingDivisionViewers = array();
        $removingDivisionGroupViewers = array();
        $removingDivisionStorageContainers = array();
        $removingDivisionSampleTypes = array();

        foreach ($uow->getScheduledEntityInsertions() as $keyEntity => $entity) {

            if ($entity instanceof DivisionEditor) {
                $division = $entity->getDivision();
                if (!$parentDivision) {
                    $parentDivision = $division;
                    $divisionEditors[] = $entity->getUser();
                }
            }

            if ($entity instanceof DivisionViewer) {
                $division = $entity->getDivision();
                if (!$parentDivision) {
                    $parentDivision = $division;
                    $divisionViewers[] = $entity->getUser();
                }
            }

            if ($entity instanceof DivisionGroupEditor) {
                $division = $entity->getDivision();
                if (!$parentDivision) {
                    $parentDivision = $division;
                    $divisionGroupEditors[] = $entity->getGroup();
                }
            }

            if ($entity instanceof DivisionGroupViewer) {
                $division = $entity->getDivision();
                if (!$parentDivision) {
                    $parentDivision = $division;
                    $divisionGroupViewers[] = $entity->getGroup();
                }
            }

            if ($entity instanceof DivisionStorageContainer) {
                $division = $entity->getDivision();
                if (!$parentDivision) {
                    $parentDivision = $division;
                    $divisionStorageContainers[] = $entity->getStorageContainer();
                }
            }

            if ($entity instanceof DivisionSampleType) {
                $division = $entity->getDivision();
                if (!$parentDivision) {
                    $parentDivision = $division;
                    $divisionSampleTypes[] = $entity->getSampleType();
                }
            }

        }

        // handle is is public edit or is public view
        foreach ($uow->getScheduledEntityUpdates() as $keyEntity => $entity) {

            if ($entity instanceof Division) {

                foreach ($uow->getEntityChangeSet($entity) as $keyField => $field) {

                    if ($keyField === 'isPublicEdit') {
                        if (!$parentDivision) {
                            $parentDivision = $entity;
                        }
                        $isPublicEdit = $field[1];
                    }

                    if ($keyField === 'isPublicView') {
                        if (!$parentDivision) {
                            $parentDivision = $entity;
                        }
                        $isPublicView = $field[1];
                    }

                    if ($keyField === 'allowAllStorageContainers') {
                        if (!$parentDivision) {
                            $parentDivision = $entity;
                        }
                        $allowAllStorageContainers = $field[1];
                    }

                    if ($keyField === 'allowAllSampleTypes') {
                        if (!$parentDivision) {
                            $parentDivision = $entity;
                        }
                        $allowAllSampleTypes = $field[1];
                    }

                }

            }

        }

        foreach ($uow->getScheduledEntityDeletions() as $keyEntity => $entity) {

            if ($entity instanceof DivisionEditor) {
                $division = $entity->getDivision();
                $user = $entity->getUser();
                if (!$parentDivision) {
                    $parentDivision = $division;
                    $removingDivisionEditors[$user->getId()] = $user;
                }
            }

            if ($entity instanceof DivisionViewer) {
                $division = $entity->getDivision();
                $user = $entity->getUser();
                if (!$parentDivision) {
                    $parentDivision = $division;
                    $removingDivisionViewers[$user->getId()] = $user;
                }
            }

            if ($entity instanceof DivisionGroupEditor) {
                $division = $entity->getDivision();
                $group = $entity->getGroup();
                if (!$parentDivision) {
                    $parentDivision = $division;
                    $removingDivisionGroupEditors[$group->getId()] = $group;
                }
            }

            if ($entity instanceof DivisionGroupViewer) {
                $division = $entity->getDivision();
                $group = $entity->getGroup();
                if (!$parentDivision) {
                    $parentDivision = $division;
                    $removingDivisionGroupViewers[$group->getId()] = $group;
                }
            }

            if ($entity instanceof DivisionStorageContainer) {
                $division = $entity->getDivision();
                $storageContainer = $entity->getStorageContainer();
                if (!$parentDivision) {
                    $parentDivision = $division;
                    $removingDivisionStorageContainers[$storageContainer->getId()] = $storageContainer;
                }
            }

            if ($entity instanceof DivisionSampleType) {
                $division = $entity->getDivision();
                $sampleType = $entity->getSampleType();
                if (!$parentDivision) {
                    $parentDivision = $division;
                    $removingDivisionSampleTypes[$sampleType->getId()] = $sampleType;
                }
            }

        }

        if (!$parentDivision) {
            return;
        }

        $currentDivisionEditors = $parentDivision->getDivisionEditors();
        $currentDivisionGroupEditors = $parentDivision->getDivisionGroupEditors();
        $currentDivisionViewers = $parentDivision->getDivisionViewers();
        $currentDivisionGroupViewers = $parentDivision->getDivisionGroupViewers();
        $currentDivisionStorageContainers = $parentDivision->getDivisionStorageContainers();
        $currentDivisionSampleTypes = $parentDivision->getDivisionSampleTypes();

        if ($isPublicView == null) {
            $isPublicView = $parentDivision->getIsPublicView();
        }

        if ($isPublicEdit == null) {
            $isPublicEdit = $parentDivision->getIsPublicEdit();
        }
        /* Storage Containers */
        if ($allowAllStorageContainers == null) {
            $allowAllStorageContainers = $parentDivision->getAllowAllStorageContainers();
        }

        /* Sample Types */
        if ($allowAllSampleTypes == null) {
            $allowAllSampleTypes = $parentDivision->getAllowAllSampleTypes();
        }

        if ($currentDivisionEditors) {

            foreach ($currentDivisionEditors as $currentDivisionEditor) {
                if (!isset($removingDivisionEditors[$currentDivisionEditor->getUser()->getId()])) {
                    $divisionEditors[] = $currentDivisionEditor->getUser();
                }
            }

        }

        if ($currentDivisionGroupEditors) {

            foreach ($currentDivisionGroupEditors as $currentDivisionGroupEditor) {
                if (!isset($removingDivisionGroupEditors[$currentDivisionGroupEditor->getGroup()->getId()])) {
                    $divisionGroupEditors[] = $currentDivisionGroupEditor->getGroup();
                }
            }

        }

        if ($currentDivisionViewers) {

            foreach ($currentDivisionViewers as $currentDivisionViewer) {
                if (!isset($removingDivisionViewers[$currentDivisionViewer->getUser()->getId()])) {
                    $divisionViewers[] = $currentDivisionViewer->getUser();
                }
            }

        }

        if ($currentDivisionGroupViewers) {

            foreach ($currentDivisionGroupViewers as $currentDivisionGroupViewer) {
                if (!isset($removingDivisionGroupViewers[$currentDivisionGroupViewer->getGroup()->getId()])) {
                    $divisionGroupViewers[] = $currentDivisionGroupViewer->getGroup();
                }
            }

        }

        if (!$parentDivision->getChildren()) {

            return;

        }

        foreach ($currentDivisionStorageContainers as $currentDivisionStorageContainer) {
            if (!isset($removingDivisionStorageContainers[$currentDivisionStorageContainer->getStorageContainer()->getId()])) {
                $divisionStorageContainers[] = $currentDivisionStorageContainer->getStorageContainer();
            }
        }

        foreach ($currentDivisionSampleTypes as $currentDivisionSampleType) {
            // echo "foreach";
            if (!isset($removingDivisionSampleTypes[$currentDivisionSampleType->getSampleType()->getId()])){
                // echo "isset";
                $divisionSampleTypes[] = $currentDivisionSampleType->getSampleType();
            }
        }


        foreach ($parentDivision->getChildren() as $child) {

            $newEditors = array();
            $newGroupEditors = array();
            $newViewers = array();
            $newGroupViewers = array();
            $newStorageContainers = array();
            $newSampleTypes = array();

            foreach ($child->getDivisionEditors() as $childEditor) {
                $uow->remove($childEditor);
            }

            foreach ($child->getDivisionGroupEditors() as $childGroupEditor) {
                $uow->remove($childGroupEditor);
            }

            foreach ($child->getDivisionViewers() as $childViewer) {
                $uow->remove($childViewer);
            }

            foreach ($child->getDivisionGroupViewers() as $childGroupViewer) {
                $uow->remove($childGroupViewer);
            }

            foreach ($child->getDivisionStorageContainers() as $childStorageContainer){
                $uow->remove($childStorageContainer);
            }

            foreach ($child->getDivisionSampleTypes() as $childSampleType){
                $uow->remove($childSampleType);
            }

            foreach ($divisionEditors as $divisionEditor) {

                $newEditor = new DivisionEditor();
                $newEditor->setDivision($child);
                $newEditor->setUser($divisionEditor);

                $uow->persist($newEditor);
                $metaEditor = $em->getClassMetadata(get_class($newEditor));
                $uow->computeChangeSet($metaEditor, $newEditor);

                $newEditors[] = $newEditor;
            }

            foreach ($divisionGroupEditors as $divisionGroupEditor) {

                $newGroupEditor = new DivisionGroupEditor();
                $newGroupEditor->setDivision($child);
                $newGroupEditor->setGroup($divisionGroupEditor);

                $uow->persist($newGroupEditor);
                $metaGroupEditor = $em->getClassMetadata(get_class($newGroupEditor));
                $uow->computeChangeSet($metaGroupEditor, $newGroupEditor);

                $newGroupEditors[] = $newGroupEditor;
            }

            foreach ($divisionViewers as $divisionViewer) {

                $newViewer = new DivisionViewer();
                $newViewer->setDivision($child);
                $newViewer->setUser($divisionViewer);

                $uow->persist($newViewer);
                $metaViewer = $em->getClassMetadata(get_class($newViewer));
                $uow->computeChangeSet($metaViewer, $newViewer);

                $newViewers[] = $newViewer;
            }

            foreach ($divisionGroupViewers as $divisionGroupViewer) {

                $newGroupViewer = new DivisionGroupViewer();
                $newGroupViewer->setDivision($child);
                $newGroupViewer->setGroup($divisionGroupViewer);

                $uow->persist($newGroupViewer);
                $metaGroupViewer = $em->getClassMetadata(get_class($newGroupViewer));
                $uow->computeChangeSet($metaGroupViewer, $newGroupViewer);

                $newGroupViewers[] = $newGroupViewer;
            }

            foreach ($divisionStorageContainers as $divisionStorageContainer) {
                $newStorageContainer = new DivisionStorageContainer();
                $newStorageContainer->setDivision($child);
                $newStorageContainer->setStorageContainer($divisionStorageContainer);

                $uow->persist($newStorageContainer);
                $metaStorageContainer = $em->getClassMetadata(get_class($newStorageContainer));
                $uow->computeChangeSet($metaStorageContainer, $newStorageContainer);

                $newStorageContainers[] = $newStorageContainer;
            }

            foreach ($divisionSampleTypes as $divisionSampleType) {
                $newSampleType = new DivisionSampleType();
                $newSampleType->setDivision($child);
                $newSampleType->setSampleType($divisionSampleType);

                $uow->persist($newSampleType);
                $metaSampleType = $em->getClassMetadata(get_class($newSampleType));
                $uow->computeChangeSet($metaSampleType, $newSampleType);

                $newSampleTypes[] = $newSampleType;
            }

            $child->setIsPublicEdit($isPublicEdit);
            $child->setIsPublicView($isPublicView);

            $child->setDivisionEditors($newEditors);
            $child->setDivisionGroupEditors($newGroupEditors);
            $child->setDivisionViewers($newViewers);
            $child->setDivisionGroupViewers($newGroupViewers);
            /* Container */
            $child->setDivisionStorageContainers($newStorageContainers);
            /* Sample Type */
            $child->setDivisionSampleTypes($newSampleTypes);

            $metaDivision = $em->getClassMetadata(get_class($child));
            $uow->computeChangeSet($metaDivision, $child);
        }

    }
}

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


    public function addToChildren($em, $entity, $className)
    {
        $classToQuery = array(
            'AppBundle\Entity\Storage\DivisionViewer' => "select id from storage.division where id not in (select id from storage.division where parent_id = :parentId and id not in (select division_id from storage.division_viewer where user_id = :linkedId))",
            'AppBundle\Entity\Storage\DivisionEditor' => "select id from storage.division where id not in (select id from storage.division where parent_id = :parentId and id not in (select division_id from storage.division_editor where user_id = :linkedId))",
            'AppBundle\Entity\Storage\DivisionStorageContainer' => "select id from storage.division where id not in (select id from storage.division where parent_id = :parentId and id not in (select division_id from storage.division_storage_container where storage_container_id = :linkedId))",
            'AppBundle\Entity\Storage\DivisionSampleType' => "select id from storage.division where id not in (select id from storage.division where parent_id = :parentId and id not in (select division_id from storage.division_sample_type where sample_type_id = :linkedId))",
            'AppBundle\Entity\Storage\DivisionGroupEditor' => "select id from storage.division where id not in (select id from storage.division where parent_id = :parentId and id not in (select division_id from storage.division_group_editor where group_id = :linkedId))",
            'AppBundle\Entity\Storage\DivisionGroupViewer' => "select id from storage.division where id not in (select id from storage.division where parent_id = :parentId and id not in (select division_id from storage.division_group_viewer where group_id = :linkedId))"
        );
        $targetClass = array(
            'AppBundle\Entity\Storage\DivisionViewer' => "User",
            'AppBundle\Entity\Storage\DivisionEditor' => "User",
            'AppBundle\Entity\Storage\DivisionStorageContainer' => "StorageContainer",
            'AppBundle\Entity\Storage\DivisionSampleType' => "SampleType",
            'AppBundle\Entity\Storage\DivisionGroupEditor' => "Group",
            'AppBundle\Entity\Storage\DivisionGroupViewer' => "Group"
        )[$className];

        if ($targetClass == "User"){
            $targetId = $entity->getUserId();
        }
        elseif ($targetClass == "StorageContainer"){
            $targetId = $entity->getStorageContainerId();
        }
        elseif ($targetClass == "SampleType"){
            $targetId = $entity->getSampleTypeId();
        }
        elseif ($targetClass == "Group"){
            $targetId = $entity->getGroupId();
        }

        $divisionRepository = $em->getRepository("AppBundle\\Entity\\Storage\\Division");
        $divisionId = $entity->getDivisionId();
        $params = array('parentId' => $divisionId, 'linkedId' => $targetId);
        $query = $classToQuery[$className];
        $localWork = false;
        $conn = $em->getConnection();

        $stmt = $conn->prepare($query);
        $stmt->bindValue('parentId', $divisionId);
        $stmt->bindValue('linkedId', $targetId);
        $stmt->execute();
        $childDivisions = $stmt->fetchAll();

        // print_r($temp);
        // die();

        foreach($childDivisions as $cd){
            $localWork = true;
            $linkerEntry = new $className();

            // $linkerEntry->setCreatedAt($entry->getCreatedAt());
            // $linkerEntry->setCreatedBy($entry->getCreatedBy());
            // $linkerEntry->setUpdatedAt($entry->getUpdatedAt());
            // $linkerEntry->setUpdatedBy($entry->getUpatedBy());

            $linkerEntry->setDivision($divisionRepository->findOneBy(array("id" => $cd)));
            if ($targetClass = 'User') {
                $linkerEntry->setUser($entity->getUser());
            }
            elseif ($targetClass == 'StorageContainer') {
                $linkerEntry->setStorageContainer($entity->getStorageContainer());
            }
            elseif ($targetClass == 'SampleType') {
                $linkerEntry->setSampleType($entity->getSampleType());
            }
            elseif ($targetClass == 'Group') {
                $linkerEntry->setGroup($entry->getGroup());
            }
            $em->persist($linkerEntry);
        }
        return $localWork;
    }


    public function removeFromChildren($em, $repo, $entity, $className)
    {

        //Repo find all that are children who have userid

        $localWork = false;

        // $childDivisions = $entity->getDivision()->getChildren();

        // foreach ($childDivisions as $cd) {

        //     if (true) {
        //         // change the value in this if statement

        //         $localWork = true;

        //         $test = new $className();

        //         $em->persist($test);
        //     }

        // }

        return $localWork;

    }

    public function updateDivisionBooleans($em, $entity)
    {

        $localWork = false;

        $childDivisions = $entity->getDivision()->getChildren();

        foreach ($childDivisions as $cd){

            if (true) {
                $localWork = true;

                // $test = new $className();

                // $em->persist($test);
            }
        }

        return $localWork;

    }


    public function onFlush(OnFlushEventArgs $args)
    {
        $em = $args->getEntityManager();
        $uow = $em->getUnitOfWork();
        $workHappened = false;

        foreach ($uow->getScheduledEntityInsertions() as $keyEntity => $entity) {

            if ($entity instanceof Division) {
                continue; // Division will be empty of other divisions when created so this option is not applicable
            }
            elseif ($entity instanceof DivisionViewer) {
                $workHappend = $this->addToChildren($em, $entity, 'AppBundle\Entity\Storage\DivisionViewer') ? true : $workHappened;
                continue;
            }
            elseif ($entity instanceof DivisionEditor) {
                $workHappend = $this->addToChildren($em, $entity, 'AppBundle\Entity\Storage\DivisionEditor') ? true : $workHappened;
                continue;
            }
            elseif ($entity instanceof DivisionStorageContainer) {
                $workHappend = $this->addToChildren($em, $entity, 'AppBundle\Entity\Storage\DivisionStorageContainer') ? true : $workHappened;
                continue;
            }
            elseif ($entity instanceof DivisionSampleType) {
                $workHappend = $this->addToChildren($em, $entity, 'AppBundle\Entity\Storage\DivisionSampleType') ? true : $workHappened;
                continue;
            }
            elseif ($entity instanceof DivisionGroupViewer) {
                $workHappend = $this->addToChildren($em, $entity, 'AppBundle\Entity\Storage\DivisionGroupViewer') ? true : $workHappened;
                continue;
            }
            elseif ($entity instanceof DivisionGroupEditor) {
                $workHappend = $this->addToChildren($em, $entity, 'AppBundle\Entity\Storage\DivisionGroupEditor') ? true : $workHappened;
                continue;
            }
        }


        foreach ($uow->getScheduledEntityDeletions() as $keyEntity => $entity) {

            if ($entity instanceof Division) {
                continue; // We are not going to allow users to delete divisions that have children -- this case should not take place
            }
            elseif ($entity instanceof DivisionViewer) {
                $workHappend = $this->removeFromChildren($em, null, $entity, 'AppBundle\Entity\Storage\DivisionSampleType') ? true : $workHappened;
                continue;
            }
            elseif ($entity instanceof DivisionEditor) {
                $workHappend = $this->removeFromChildren($em, null, $entity, 'AppBundle\Entity\Storage\DivisionEditor') ? true : $workHappened;
                continue;
            }
            elseif ($entity instanceof DivisionStorageContainer) {
                $workHappend = $this->removeFromChildren($em, null, $entity, 'AppBundle\Entity\Storage\DivisionStorageContainer') ? true : $workHappened;
                continue;
            }
            elseif ($entity instanceof DivisionSampleType) {
                $workHappend = $this->removeFromChildren($em, null, $entity, 'AppBundle\Entity\Storage\DivisionSampleType') ? true : $workHappened;
                continue;
            }
            elseif ($entity instanceof DivisionGroupEditor) {
                $workHappend = $this->removeFromChildren($em, null, $entity, 'AppBundle\Entity\Storage\DivisionGroupEditor') ? true : $workHappened;
                continue;
            }
            elseif ($entity instanceof DivisionGroupViewer) {
                $workHappend = $this->removeFromChildren($em, null, $entity, 'AppBundle\Entity\Storage\DivisionGroupViewer') ? true : $workHappened;
                continue;
            }
        }

        foreach ($uow->getScheduledEntityUpdates() as $keyEntity => $entity) {

            if ($entity instanceof Division) {
                $workHappened = $this->updateDivisionBooleans($em, $entity) ? true : $workHappened;
            }

        }

        // If a new entity was modified then we need to flush the entity manager
        if($workHappened){
            $em->flush();
        }

    }
}

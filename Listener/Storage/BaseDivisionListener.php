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
    Modified by Taylor Jones

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
// Using the entity manager is simply too slow....

class BaseDivisionListener
{
    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }

    public function propToChildren($conn, $table, $prototype, $entityId, $divisions)
    {
        $itemArr = array();
        foreach ($divisions as $division) {
            $itemArr[] = "(".$entityId.", ".$division.")";
        }
        $valString = implode(', ', $itemArr);
        $query = "INSERT INTO ".$table." ".$prototype." VALUES ".$valString.";";
        $stmt = $conn->prepare($query);
        $stmt->execute();
    }

    public function removeFromChildren($conn, $table, $entTypeId, $entityId, $divisions)
    {
        $itemArr = array();
        foreach ($divisions as $division) {
            $itemArr[] = "(".$entityId.", ".$division.")";
        }
        $divString = implode(', ', $itemArr);
        $query = "DELETE FROM ".$table." WHERE id IN ".$divString." AND ".$entTypeId."=".$entityId.";";
        $stmt = $conn->prepsare($query);
        $stmt->execute();
    }

    public function buildChildList($conn, $startDivisionId, $condition = 'id IS NOT NULL')
    {
        $parentArray = array();
        $currCount = 0;

        $mapFunc =  function($temp){
            return $temp['id'];
        };

        do {
            $currCount = count($parentArray);
            $arrString = $currCount > 0 ? ' OR parent_id IN  ('.implode(', ', $parentArray).') ' : '';
            $query = "SELECT id FROM storage.division WHERE (parent_id = ".$startDivisionId.$arrString." ) AND ".$condition.";";
            $stmt = $conn->prepare($query);
            $stmt->execute();
            $testSet = $stmt->fetchAll();

            $parentArray = array_map($mapFunc, $testSet);
        } while ($currCount != count($parentArray));

        return $parentArray;
    }

    public function onFlush(OnFlushEventArgs $args)
    {
        $em = $args->getEntityManager();
        $uow = $em->getUnitOfWork();
        $conn = $em->getConnection();

        foreach ($uow->getScheduledEntityInsertions() as $keyEntity => $entity) {

            if ($entity instanceof Division) {
                continue;
            }
            elseif ($entity instanceof DivisionViewer) {
                $divisionId = $entity->getDivisionId();
                $entityId = $entity->getUserId();
                $condition = 'id NOT IN (SELECT id FROM storage.division_viewer WHERE division_id = '.$divisionId.' AND user_id = '.$entityId.')';
                $divisionList = $this->buildChildList($conn, $divisionId, $condition);
                // $this->propToChildren($conn, 'storage.division_viewer', '(user_id, division_id)', $entityId, $divisonList);
            }
            elseif ($entity instanceof DivisionEditor) {
                $divisionId = $entity->getDivisionId();
                $entityId = $entity->getUserId();
                $condition = 'id NOT IN (SELECT id FROM storage.division_editor WHERE division_id = '.$divisionId.' AND user_id = '.$entityId.')';
                $divisionList = $this->buildChildList($conn, $divisionId, $condition);
                // $this->propToChildren($conn, 'storage.division_editor', '(user_id, division_id)', $entityId, $divisonList);
            }
            elseif ($entity instanceof DivisionStorageContainer) {
                $divisionId = $entity->getDivisionId();
                $entityId = $entity->getStorageContainerId();
                $condition = 'id NOT IN (SELECT id FROM storage.division_storage_container WHERE division_id = '.$divisionId.' AND storage_container_id = '.$entityId.')';
                $divisionList = $this->buildChildList($conn, $divisionId, $condition);
                // $this->propToChildren($conn, 'storage.division_storage_container', '(storage_container_id, division_id)', $entityId, $divisonList);
            }
            elseif ($entity instanceof DivisionSampleType) {
               $divisionId = $entity->getDivisionId();
                $entityId = $entity->getSampleTypeId();
                $condition = 'id NOT IN (SELECT id FROM storage.division_sample_type WHERE division_id = '.$divisionId.' AND sample_type_id = '.$entityId.')';
                $divisionList = $this->buildChildList($conn, $divisionId, $condition);
                // $this->propToChildren($conn, 'storage.division_sample_type', '(sample_type_id, division_id)', $entityId, $divisonList);
            }
            elseif ($entity instanceof DivisionGroupViewer) {
                $divisionId = $entity->getDivisionId();
                $entityId = $entity->getGroupId();
                echo $divisionId;
                $condition = 'id NOT IN (SELECT id FROM storage.division_group_viewer WHERE division_id = '.$divisionId.' AND group_id = '.$entityId.')';
                $divisionList = $this->buildChildList($conn, $divisionId, $condition);
                // $this->propToChildren($conn, 'storage.division_group_viewer', '(group_id, division_id)', $entityId, $divisonList);
            }
            elseif ($entity instanceof DivisionGroupEditor) {
                $divisionId = $entity->getDivisionId();
                echo $divisionId;
                $entityId = $entity->getGroupId();
                echo $entityId;
                $condition = 'id NOT IN (SELECT id FROM storage.division_group_editor WHERE division_id = '.$divisionId.' AND group_id = '.$entityId.')';
                $divisionList = $this->buildChildList($conn, $divisionId, $condition);
                // $this->propToChildren($conn, 'storage.division_group_editor', '(group_id, division_id)', $entityId, $divisonList);
            }
        }

        foreach ($uow->getScheduledEntityDeletions() as $keyEntity => $entity) {

            // if ($entity instanceof Division) {
            //     continue; // We are not going to allow users to delete divisions that have children -- this case should not take place
            // }
            // elseif ($entity instanceof DivisionViewer) {
            //     $condition = '';
            //     $divisionId = $entity->getDivisionId();
            //     $entityId = $entity->getUserId();
            //     $divisionList = $this->buildChildList($conn, $divisionId, $condition);
            // }
            // elseif ($entity instanceof DivisionEditor) {
            //     $condition = '';
            //     $divisionId = $entity->getDivisionId();
            //     $entityId = $entity->getUserId();
            //     $divisionList = $this->buildChildList($conn, $divisionId, $condition);
            // }
            // elseif ($entity instanceof DivisionStorageContainer) {
            //     $condition = '';
            //     $divisionId = $entity->getDivisionId();
            //     $entityId = $entity->getStorageContainerId();
            //     $divisionList = $this->buildChildList($conn, $divisionId, $condition);
            // }
            // elseif ($entity instanceof DivisionSampleType) {
            //     $condition = '';
            //     $divisionId = $entity->getDivisionId();
            //     $entityId = $entity->getSampleTypeId();
            //     $divisionList = $this->buildChildList($conn, $divisionId, $condition);
            // }
            // elseif ($entity instanceof DivisionGroupEditor) {
            //     $divisionId = $entity->getDivisionId();
            //     $condition = '';
            //     $entityId = $entity->getGroupId();
            //     $divisionList = $this->buildChildList($conn,$divisionId, $condition);
            // }
            // elseif ($entity instanceof DivisionGroupViewer) {
            //     $condition = '';
            //     $divisionId = $entity->getDivisionId();
            //     $entityId = $entity->getGroupId();
            //     $divisionList = $this->buildChildList($conn,$divisionId, $condition);
            // }
        }

        foreach ($uow->getScheduledEntityUpdates() as $keyEntity => $entity) {

            // if ($entity instanceof Division) {
            //     // $workHappened = $this->updateDivisionBooleans($em, $entity) ? true : $workHappened;
            // }
        }
    }
}

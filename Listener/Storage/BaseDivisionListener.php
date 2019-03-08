<?php

namespace Carbon\ApiBundle\Listener\Storage;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\HttpFoundation\RequestStack;

use AppBundle\Entity\Storage\Division;
use Carbon\ApiBundle\Entity\Storage\BaseDivisionEditor;
use Carbon\ApiBundle\Entity\Storage\BaseDivisionViewer;
use Carbon\ApiBundle\Entity\Storage\BaseDivisionGroupEditor;
use Carbon\ApiBundle\Entity\Storage\BaseDivisionGroupViewer;
use Carbon\ApiBundle\Entity\Storage\BaseDivisionStorageContainer;
use Carbon\ApiBundle\Entity\Storage\BaseDivisionSampleType;
use Carbon\ApiBundle\Entity\Storage\BaseDivisionAccessGovernor;


// Outstanding concerns:
    // I don't know how we should handle toggling isPublicEdit/allowAllStorageContainer...etc booleans when cascading permissiosn.
    // Trampling permissions should have toggling handled propertly.

//get_class

/*
    Base Division Listener
    Written by Andre Jon Branchizio
    Modified by Taylor Jones


    Originally we chose to make it so that the permissions of chidren divisions would be overwritten by changes to their immediate parent
    This has been modified so that the changes that take place with the parent will also be applied to the children

    Example :
        Setup (before any action has been taken):
            Division 1 has 2 children Division and Division 3

            Division permissions are as follows:
                Division 1 permissions editable by andre, allows hybridoma, allows vials
                Division 2 Editable by andre, allows cell supernatant, allows eppendorf tubes
                Division 3 Editable by Dr crowe, allows protein, allows vials

        Action:
            Add Taylor to editors for  Division 1

        Old system outcome:
            Division 1 and all of its children are editable by andre + taylor, allow hybridoma, allows vials -- children below that level would not be affected.

        New system outcome:
            Division 1 permissions editable by andre & taylor, allows hybridoma, allows vials
            Division 2 Editable by andre & taylor, allows cell supernatant, allows eppendorf tubes
            Division 3 Editable by Dr Crowe & taylor, allows protein, allows vials

    Later versions may make it possible to select the method fo cascading which is going to be used for this update operation -- I think that this will present problems because users will not take the time to get to understand the tools that we are creating

*/

class BaseDivisionListener
{
    private $logger;
    private $runPostFlush = false;
    private $request_stack;

    public function __construct(Logger $logger, RequestStack $request_stack)
    {
        $this->logger = $logger;
        $this->request_stack = $request_stack;
    }

    public function propToChildren($conn, $table, $prototype, $entityId, $divisions)
    {
        $itemArr = array();
        foreach ($divisions as $division) {
            $itemArr[] = "(".$entityId.", ".$division.")";
        }

        if (count($itemArr) > 0) {
            $valString = implode(', ', $itemArr);
            $query = "INSERT INTO ".$table." ".$prototype." VALUES ".$valString.";";
            $stmt = $conn->prepare($query);
            $stmt->execute();
        }
    }

    // Specify divisions that should have all of their children removed.
    public function removeAllChildLinks($conn, $list=array())
    {
        $listImplode = '('.implode(',',$list).')';

        $queries = array(
            "DELETE from storage.division_storage_container where division_id in ".$listImplode,
            "DELETE from storage.division_sample_type where division_id in ".$listImplode,
            "DELETE from storage.division_editor where division_id in ".$listImplode,
            "DELETE from storage.division_viewer where division_id in ".$listImplode,
            "DELETE from storage.division_group_editor where division_id in ".$listImplode,
            "DELETE from storage.division_group_viewer where division_id in ".$listImplode,
        );

        foreach ($queries as $query) {
            $conn->prepare($query)->execute();
        }

    }

    public function copyAllLinks($conn, $parent, $childList)
    {
        $tables = array(
                'storage.division_editor' => 'user_id',
                'storage.division_viewer' => 'user_id',
                'storage.division_group_editor' => 'group_id',
                'storage.division_group_viewer' => 'group_id',
                'storage.division_storage_container' => 'storage_container_id',
                'storage.division_sample_type' => 'sample_type_id'
        );

        foreach ($tables as $table => $field) {

            $parentQuery = "SELECT " . $field . " from " . $table . " where division_id = " . $parent;
            $stmt = $conn->prepare($parentQuery);
            $stmt->execute();

            foreach ($stmt->fetchAll() as $result) {
                $this->propToChildren($conn, $table, '('.$field.', '.'division_id)', $result[$field], $childList);
            }
        }
    }

    public function removeFromChildren($conn, $table, $entTypeId, $entityId, $divisions)
    {
        if (count($divisions) > 0) {
            $divString = " ( ".implode(', ', $divisions)." ) ";

            $query = "DELETE FROM ".$table." WHERE division_id IN ".$divString." AND ".$entTypeId."=".$entityId;
            $stmt = $conn->prepare($query);
            $stmt->execute();
        }
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

    /*
        Updates the booleans associated with a list of divisiosn to reflect a set of arguments that is passed in.
        Booleans should be an associative array mapping field name to a value that should go in that field.
            Ex.
                array(
                    is_public_edit => "true"
                    is_public_view => "false"
                    ...
                    )
                If a key does not exist in the array then the value that the record currently holdes will not be altered.
    */
    public function bulkUpdateBooleans($conn, $booleans = array(), $divisions)
    {
        if (count($booleans) > 0){

            $strarr = array();

            foreach ($booleans as $key => $value)
            {

                $strarr[] = $key.' = '.$value;
            }

            $valString = '('.implode(', ', $divisions).')';

            $strstr = implode(', ', $strarr);

            $query = "UPDATE storage.division set ".$strstr." where id in ".$valString;
            $stmt = $conn->prepare($query);
            $stmt->execute();
        }
    }

    // Directly calling getDivisionId() was not working -- have to call get division then getid... Don't know why that would be the case...
    public function onFlush(OnFlushEventArgs $args)
    {
        $em = $args->getEntityManager();
        $uow = $em->getUnitOfWork();
        $conn = $em->getConnection();
        $request =  $this->request_stack->getCurrentRequest();

        if(!is_object($request)){
            return;
        }

        $request = json_decode($request->getContent(), true);


        if (!array_key_exists('propagationBehavior', $request)){
            return;
        }

        $propMethod = explode(" ",$request['propagationBehavior'])[0];

        if($propMethod == "Default"){
            return;
        }

        // $cascade =  $request['cascade'];

        // If we are creating an entity it will not have an id or children and it will have no need for any sort of cascading
        if (!array_key_exists('id', $request)) {

            return;

        }

        if ($propMethod == "Cascade") {

            // echo "cascade set";
            // die();

            foreach ($uow->getScheduledEntityInsertions() as $keyEntity => $entity) {

                echo get_class($entity);
                die();

                if ($entity instanceof Division) {
                    continue;
                }
                elseif ($entity instanceof BaseDivisionAccessGovernor){
                    $divisionId = $entity->getDivisionId();
                    $entityId = $entity->getAccessGovernorId();
                    $tableName = $em->getClassMetaData(get_class($entity))->getTableName();


                }
/*
                elseif ($entity instanceof BaseDivisionViewer) {
                    $divisionId = $entity->getDivision()->getId();
                    $entityId = $entity->getUser()->getId();
                    $condition = 'id NOT IN (SELECT division_id FROM storage.division_viewer WHERE user_id = '.$entityId.')';
                    $divisionList = $this->buildChildList($conn, $divisionId, $condition);
                    $this->propToChildren($conn, 'storage.division_viewer', '(user_id, division_id)', $entityId, $divisionList);
                }
                elseif ($entity instanceof BaseDivisionEditor) {
                    $divisionId = $entity->getDivision()->getId();
                    $entityId = $entity->getUser()->getId();
                    $condition = 'id NOT IN (SELECT division_id FROM storage.division_editor WHERE  user_id = '.$entityId.')';
                    $divisionList = $this->buildChildList($conn, $divisionId, $condition);
                    $this->propToChildren($conn, 'storage.division_editor', '(user_id, division_id)', $entityId, $divisionList);
                }
                elseif ($entity instanceof BaseDivisionStorageContainer) {
                    $divisionId = $entity->getDivision()->getId();
                    $entityId = $entity->getStorageContainer()->getId();
                    $condition = 'id NOT IN (SELECT division_id FROM storage.division_storage_container WHERE  storage_container_id = '.$entityId.')';
                    $divisionList = $this->buildChildList($conn, $divisionId, $condition);
                    $this->propToChildren($conn, 'storage.division_storage_container', '(storage_container_id, division_id)', $entityId, $divisionList);
                }
                elseif ($entity instanceof BaseDivisionSampleType) {
                    $divisionId = $entity->getDivision()->getId();
                    $entityId = $entity->getSampleType()->getId();
                    $condition = 'id NOT IN (SELECT division_id FROM storage.division_sample_type WHERE  sample_type_id = '.$entityId.')';
                    $divisionList = $this->buildChildList($conn, $divisionId, $condition);
                    $this->propToChildren($conn, 'storage.division_sample_type', '(sample_type_id, division_id)', $entityId, $divisionList);
                }
                elseif ($entity instanceof BaseDivisionGroupViewer) {
                    $divisionId = $entity->getDivision()->getId();
                    $entityId = $entity->getGroup()->getId();
                    $condition = 'id NOT IN (SELECT division_id FROM storage.division_group_viewer WHERE group_id = '.$entityId.')';
                    $divisionList = $this->buildChildList($conn, $divisionId, $condition);
                    $this->propToChildren($conn, 'storage.division_group_viewer', '(group_id, division_id)', $entityId, $divisionList);
                }
                elseif ($entity instanceof BaseDivisionGroupEditor) {
                    $divisionId = $entity->getDivision()->getId();
                    $entityId = $entity->getGroup()->getId();
                    $condition = 'id NOT IN (SELECT division_id FROM storage.division_group_editor WHERE group_id = '.$entityId.')';
                    $divisionList = $this->buildChildList($conn, $divisionId, $condition);
                    $this->propToChildren($conn, 'storage.division_group_editor', '(group_id, division_id)', $entityId, $divisionList);
                }
*/
            }

            foreach ($uow->getScheduledEntityDeletions() as $keyEntity => $entity) {

                if ($entity instanceof Division) {
                    continue; // We are not going to allow users to delete divisions that have children -- this case should not take place
                }
                elseif ($entity instanceof BaseDivisionAccessGovernor){

                }
/*
                elseif ($entity instanceof BaseDivisionViewer) {
                    $divisionId = $entity->getDivision()->getId();
                    $entityId = $entity->getUser()->getId();
                    $condition = 'id IN (SELECT division_id FROM storage.division_viewer WHERE user_id = '.$entityId.')';
                    $divisionList = $this->buildChildList($conn, $divisionId, $condition);
                    $this->removeFromChildren($conn, 'storage.division_viewer', 'user_id', $entityId, $divisionList);
                }
                elseif ($entity instanceof BaseDivisionEditor) {
                    $divisionId = $entity->getDivision()->getId();
                    $entityId = $entity->getUser()->getId();
                    $condition = 'id IN (SELECT division_id FROM storage.division_editor WHERE user_id = '.$entityId.')';
                    $divisionList = $this->buildChildList($conn, $divisionId, $condition);
                    $this->removeFromChildren($conn, 'storage.division_editor', 'user_id', $entityId, $divisionList);
                }
                elseif ($entity instanceof BaseDivisionStorageContainer) {
                    $divisionId = $entity->getDivision()->getId();
                    $entityId = $entity->getStorageContainer()->getId();
                    $condition = 'id IN (SELECT division_id FROM storage.division_storage_container WHERE storage_container_id = '.$entityId.')';
                    $divisionList = $this->buildChildList($conn, $divisionId, $condition);
                    $this->removeFromChildren($conn, 'storage.division_storage_container', 'storage_container_id', $entityId, $divisionList);
                }
                elseif ($entity instanceof BaseDivisionSampleType) {
                    $divisionId = $entity->getDivision()->getId();
                    $entityId = $entity->getSampleType()->getId();
                    $condition = 'id IN (SELECT division_id FROM storage.division_sample_type WHERE sample_type_id = '.$entityId.')';
                    $divisionList = $this->buildChildList($conn, $divisionId, $condition);
                    $this->removeFromChildren($conn, 'storage.division_sample_type', 'sample_type_id', $entityId, $divisionList);
                }
                elseif ($entity instanceof BaseDivisionGroupEditor) {
                    $divisionId = $entity->getDivision()->getId();
                    $entityId = $entity->getGroup()->getId();
                    $condition = 'id IN (SELECT division_id FROM storage.division_group_editor WHERE group_id = '.$entityId.')';
                    $divisionList = $this->buildChildList($conn,$divisionId, $condition);
                    $this->removeFromChildren($conn, 'storage.division_group_editor', 'group_id', $entityId, $divisionList);
                }
                elseif ($entity instanceof BaseDivisionGroupViewer) {
                    $divisionId = $entity->getDivision()->getId();
                    $entityId = $entity->getGroup()->getId();
                    $condition = 'id IN (SELECT division_id FROM storage.division_group_viewer WHERE group_id = '.$entityId.')';
                    $divisionList = $this->buildChildList($conn,$divisionId, $condition);
                    $this->removeFromChildren($conn, 'storage.division_group_viewer', 'group_id', $entityId, $divisionList);
                }
*/
            }

            foreach ($uow->getScheduledEntityUpdates() as $keyEntity => $entity) {

                if ($entity instanceof Division && $entity->getId() == $request['id']) { // Only want to call this portion for the update which was created by the request -- don't want to end up in an infinite loop...
                    $divisionMetadata = $em->getClassMetaData(get_class($entity));

                    $accessorBooleans = array();
                    $id = $entity->getId();

                    foreach ( $uow->getEntityChangeset($entity) as $keyField => $field){
                        if ( in_array($keyField, array('isPublicEdit', 'isPublicView', 'allowAllStorageContainers', 'allowAllSampleTypes')) ) {
                            $accessorBooleans[$divisionMetadata->getColumnName($keyField)] = $field[1] ? "true" : "false";
                            // $accessorBooleans[] = array($keyField => $field[1]);
                        }
                    }

                    $childList = $this->buildChildList($conn, $request['id']);
                    $this->bulkUpdateBooleans($conn, $accessorBooleans, $childList);

                }
            }

            // What should the behavior be when toggling the booleans of the children?
        }
        else { // If cascade  is false then we need to get ready to trample stuff in the postFlush

            foreach (array_merge( array_merge($uow->getScheduledEntityUpdates(), $uow->getScheduledEntityInsertions() ), $uow->getScheduledEntityDeletions()) as $keyEntity => $entity){
                if ($entity instanceof Division || $entity instanceof BaseDivisionAccessGovernor) {
                    $this->runPostFlush = true;
                }
            }

        }
    }

    public function postFlush(PostFlushEventArgs $args)
    {

        if ($this->runPostFlush == false) {
            return;
        }

        $em = $args->getEntityManager();
        $conn = $em->getConnection();
        $request =  json_decode($this->request_stack->getCurrentRequest()->getContent(), true);

        if(!is_object($request)){
            return;
        }

        if ($propMethod == 'Trample') {

            if (!array_key_exists('cascade', $request)) {
                return;
            }

            if ($request['cascade'] != true) {
                $divRepo = $em->getRepository('AppBundle\Entity\Storage\Division');
                $divOfInterest = $divRepo->findOneById($request['id']);

                $propertyList = array(
                    'is_public_view' => $divOfInterest->getIsPublicView() == true ? "true" : "false",
                    'is_public_edit' => $divOfInterest->getIsPublicEdit() == true ? "true" : "false",
                    'allow_all_sample_types' => $divOfInterest->getAllowAllSampleTypes() == true ? "true" : "false",
                    'allow_all_storage_containers' => $divOfInterest->getAllowAllStorageContainers() == true ? "true" : "false"
                );

                $childList = $this->buildChildList($conn, $request['id']);

                $this->bulkUpdateBooleans($conn, $propertyList, $childList);

                $this->removeAllChildLinks($conn, $childList);
                $this->copyAllLinks($conn, $request['id'], $childList); // This is not working as intended.
            }
        }

    }
}

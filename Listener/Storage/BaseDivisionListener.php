<?php

namespace Carbon\ApiBundle\Listener\Storage;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\HttpFoundation\RequestStack;

// Base classes in common
use Carbon\ApiBundle\Entity\Storage\BaseDivision;
use Carbon\ApiBundle\Entity\Storage\BaseDivisionEditor;
use Carbon\ApiBundle\Entity\Storage\BaseDivisionViewer;
use Carbon\ApiBundle\Entity\Storage\BaseDivisionGroupEditor;
use Carbon\ApiBundle\Entity\Storage\BaseDivisionGroupViewer;
use Carbon\ApiBundle\Entity\Storage\BaseDivisionStorageContainer;
use Carbon\ApiBundle\Entity\Storage\BaseDivisionSampleType;
use Carbon\ApiBundle\Entity\Storage\BaseDivisionAccessGovernor;

// VIOLATION -- This is located in command and it is dependant upon certain functionality being located in a known namespace outside of common.
use AppBundle\Entity\Storage\Division as SpecificDivName;
    // CollectionType is used to make the rest of this code as generic as possible.
    // Collection type must extend BaseDivision -- This should be changed later on... -- There should be an abstract class above base division in all honesty

// Outstanding concerns:
    // I don't know how we should handle toggling isPublicEdit/allowAllStorageContainer...etc booleans when cascading permissiosn.
    // Trampling permissions should have toggling handled propertly.

// get_class

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


    public function buildChildList($conn, $startDivisionId, $condition = 'deleted_at IS NULL')
    {
        $parentArray = array();
        $currCount = 0;

        $mapFunc =  function($temp){
            return $temp['id'];
        };

        do {
            $currCount = count($parentArray);

            $arrString = $currCount > 0 ? ' OR parent_id IN  ('.implode(', ', $parentArray).') ' : '';
            $query = "SELECT id FROM storage.division WHERE (parent_id = ".$startDivisionId.$arrString." ) AND ".$condition.";"; // VIOLATION -- assumes that they are using an sql based datababase
            $stmt = $conn->prepare($query);
            $stmt->execute();
            $testSet = $stmt->fetchAll();

            $parentArray = array_map($mapFunc, $testSet);

        } while ($currCount != count($parentArray));

        // echo count($parentArray);
        // die();
        return $parentArray;
    }


    // use array_diff in order to fine the elements which are in the first array but not the second.
    private function cascadeToChildren($em, $divisionId, $entity)
    {

        // die();
        //getChildrenQuery on repository

        $divRepo = $em->getRepository(get_class(new SpecificDivName()));
        $divQb = $divRepo->createQueryBuilder($alias = "divs");

        $accessorRepo = $em->getRepository(get_class($entity));
        $accessorQb = $accessorRepo->createQueryBuilder($alias = "accessors");

        $children = array();
        $initChildren = array($divRepo->find($divisionId));

        // do {

        //     $numEntries = count($children);
        //     $children2 = array();

        //     foreach ($initChildren as $child) {
        //         $temp  = $divRepo->getChildrenQuery($child, true)->getResult();

        //         foreach ($temp as $t){
        //             $children2[] = $t;
        //         }
        //     }

        //     $initChildren = $children2;
        //     $children = $children + $children2;

        // } while ($numEntries != count($children));

        // $valArr = array();
        // foreach ($children as $child){
        //     $valArr[] = $child->getId();
        // }

       // return $valArr;


        // echo count($children);

        // die();

        // echo $entity->getDivision()->getId();

        // This will be uncommented on the final version
        // $node = $divRepo->find(1);

        // $childNodes = $divRepo->getChildrenQuery($node, true)->getResult();

        // $childNodes = $this->getEntityRepository()->getChildrenQuery($nodes[0], true)->getResult();

        // $results = $accessorQb->getQuery()->getResult();

        // $count = 0;

        // foreach ($results as $result){
        //     echo $count;
        //     $count++;
        // }

        // die();

    }

    // Directly calling getDivisionId() was not working -- have to call get division then getid... Don't know why that would be the case...
    public function onFlush(OnFlushEventArgs $args)
    {

        $em = $args->getEntityManager();
        $uow = $em->getUnitOfWork();
        $conn = $em->getConnection();
        $request =  $this->request_stack->getCurrentRequest();

        if (!is_object($request)) {
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

        // If this is a creation it will not have an id
            // If it is a creation there is also no need to cascade any permissions
        if (!array_key_exists('id', $request)) {
            return;
        }

        if ($propMethod == "Cascade") {

            foreach ($uow->getScheduledEntityInsertions() as $keyEntity => $entity) {

                if ($entity instanceof BaseDivision) {
                    continue;
                }
                elseif ($entity instanceof BaseDivisionAccessGovernor) {

                    $divisionId = $request['id'];
                    // $entityId = $entity->getAccessGovernor()->getId();

                    $this->cascadeToChildren($em, $divisionId, $entity);
                    die();

                    // Build child node set



                    // $tableName = $classMetadata->getTableName();
                    // echo get_class($entity);
                    // echo $tableName;

                }
            }

            foreach ($uow->getScheduledEntityDeletions() as $keyEntity => $entity) {
                if ($entity instanceof BaseDivision) {
                    continue; // We are not going to allow users to delete divisions that have children -- this case should not take place
                }
                elseif ($entity instanceof BaseDivisionAccessGovernor){
                    // get class name
                    // get class metadata
                    // get table
                }
            }

            foreach ($uow->getScheduledEntityUpdates() as $keyEntity => $entity) {

                if ($entity instanceof BaseDivision && $entity->getId() == $request['id']) { // Only want to call this portion for the update which was created by the request -- don't want to end up in an infinite loop...
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

// When cascade is on...
    // What should the boolean cascade methodolgy look like?
        }
        else { // If cascade  is false then we need to get ready to trample stuff in the postFlush
            foreach (array_merge( array_merge($uow->getScheduledEntityUpdates(), $uow->getScheduledEntityInsertions() ), $uow->getScheduledEntityDeletions()) as $keyEntity => $entity){
                if ($entity instanceof BaseDivision || $entity instanceof BaseDivisionAccessGovernor) {
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
        $request = $this->request_stack->getCurrentRequest();

        if(!is_object($request)){
            return;
        }

        $request =  json_decode($request->getContent(), true);

        if (!array_key_exists('propagationBehavior', $request)){
            return;
        }

        if ($propMethod == 'Trample') {

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

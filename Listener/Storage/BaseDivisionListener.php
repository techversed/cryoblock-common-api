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

// VIOLATION -- This is located in command and it is dependant upon certain functionality being located in a known namespace outside of common. -- This can be fixed by making this class abstract and extending it locally in the crowelab project directory...
// use AppBundle\Entity\Storage\Division as SpecificDivName;
    // CollectionType is used to make the rest of this code as generic as possible.
    // Collection type must extend BaseDivision -- This should be changed later on... -- There should be an abstract class above base division in all honesty


// VIOLATION --
/*
    This listener makes the assumption that the database which is being used is SQL based -- The performance of this apporoach is much faster than any alternative however this is VERY bad behavior in common
    --  I would like to get to the point where we do not make any assumptions in common about the nature of the implementation's database...
    -- I think that this could be done by having a series of steps which are implemented using
*/

// Outstanding concerns:
    // I don't know how we should handle toggling isPublicEdit/allowAllStorageContainer...etc booleans when cascading permissions.
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

abstract class BaseDivisionListener
{

    // This function must be call a constructor of the type of division taht is being used in this hierarchy.
    // By default we have only used sample storage divisions with this type of listener but I am trying to make it so that the propagation behavior which is implemented in this file can easily be brought to any set of divisions that are structured as a tree
    //
    abstract protected function createDivisionOfSpecificType();

    private $logger;
    private $runPostFlush = false;
    private $request_stack;

    public function __construct(Logger $logger, RequestStack $request_stack)
    {
        $this->logger = $logger;
        $this->request_stack = $request_stack;
    }

    // VIOLATION -- this should really use the doctrine query language but this is just so fast and straight forward that it is hard to bring myself to replace it.
    // This is called to build lists of children instead of using doctrine query language or the ->createChildQuery which is present in the division repository -- I ran some benchamrks and the version that queried the database directly ran 35 times faster in the little test that I perormed. about 20 ms instead of 70 ms

    // Since this tree building implementation is so much faster than building a similar tree with frequency entity manager calls it would probably be a good idea to move this into one of the base repository classes.

    /*
        $conn = database connection
        $tablename = the table table that is being queried (in this case it will be storage.divison -- kept abstract so that we can move things over into a more refined version of common
        $startDivisionId = The base division that we want the children of
        $condition = The thing that needs to be true == in this case we are using deleted_at IS NULL -- but this can be overridden
    */
    private function buildChildList($conn, $tablename, $startDivisionId, $condition = 'deleted_at IS NULL')
    {

        $parentArray = array();
        $currCount = 0;

        $mapFunc =  function($temp){
            return $temp['id'];
        };

        do {
            $currCount = count($parentArray);

            $arrString = $currCount > 0 ? ' OR parent_id IN  ('.implode(', ', $parentArray).') ' : '';
            $query = "SELECT id FROM ".$tablename." WHERE (parent_id = ".$startDivisionId.$arrString." ) AND ".$condition.";"; // VIOLATION -- assumes that they are using an sql based datababase
            $stmt = $conn->prepare($query);
            $stmt->execute();
            $testSet = $stmt->fetchAll();

            $parentArray = array_map($mapFunc, $testSet);

        } while ($currCount != count($parentArray));

        return $parentArray;
    }

    // This is a disgusting function call... It needs to be this way in order to maintain flexbility but if we were only going to be using it for storage divisions a lot of stuff could be hard coded instead...
    /*
        $conn = database connection
        $divTableName = table name for the divsion that you are searhcing,
        $divCondition = lets the user provide a condition that applies to all parent entries which are returned
        $childList -
        $accessorTable -
        $accessorColumn -
        $accessorCondition - Anything else that needs to be appended to the thing that is being queried...
        $accessorValue - The value that needs to be present in the column to move forwards
            hasAccessor should be true when you want a list of children that have the accessor in question
            hasAccessor should be false when you want a list of children that do not have the accessor in question
    */
    private function reduceDivisionList($conn, $divTableName, $divTableColumn, $divCondition = "deleted_at IS NOT NULL", $childList, $accessorTable, $accessorColumn, $accessorValue, $accessorCondition = "deleted_at IS NOT NULL", $hasAccessor = false){

        $strlist = '('.implode(', ', $childList).')';

        $mapFunc =  function($temp){
            return $temp['id'];
        };

        $hasAccessor ? '' : 'NOT';

        $query = "SELECT d.".$divTableColumn." FROM ".$divTableName." AS d WHERE d.".$divTableColumn." IN ". $strlist." AND d.".$divTableColumn." ".$hasAccessor." IN (SELECT division_id FROM ".$accessorTable." WHERE " . $accessorColumn. " = ".$accessorValue." AND ".$accessorCondition.") AND ".$divCondition;
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $testSet = $stmt->fetchAll();

        return array_map($mapFunc, $testSet);
    }


    // We should probably make this abstract at some point and just move this implementation over to the class that implements this functionality...
    // use array_diff in order to fine the elements which are in the first array but not the second.
    // $em - entity manager
    // $divisionId = the id of the parent division
    // $entity = the divisionAccessor entity that is being added.
    // $addOrDelete
        // True if you want to add the given property to the children that do not have it yet.
        // False if you want to remove the given accessor from the children that already have it.
    private function cascadeToChildren($em, $divisionId, $entity, $addOrDelete = true)
    {

        $divClass = get_class($this->createDivisionOfSpecificType());
        $divRepo = $em->getRepository($divClass);
        $divisionMetadata = $em->getClassMetadata($divClass);
        $divQb = $divRepo->createQueryBuilder($alias = "divs");

        $accessorRepo = $em->getRepository(get_class($entity));
        $accessorQb = $accessorRepo->createQueryBuilder($alias = "accessors");
        $accessorMetadata = $em->getClassMetadata(get_class($entity));

        $childList = $this->buildChildList($em->getConnection(), $divisionMetadata->getTableName(), $divisionId);

        // VIOLATION -- should really avoid hard coding the divisison_id -- it is fine here since we are the only ones using this class -- When this is really common it should be avoided.
        $needyChildren = $this->reduceDivisionList($em->getConnection(), $divisionMetadata->getTableName(), 'id', 'deleted_at IS NOT NULL', $childList, $accessorMetadata->getTableName(), $entity->getAccessorColumnName(), $entity->getAccessGovernor()->getId(), "deleted_at IS NOT NULL", $addOrDelete ? false : true);

        $ag = $entity->getAccessGovernor->getId();
        $agCol = $entity->getAccessorColumnName();

        if ($addOrDelete == true) {
            $pairs = array();
            foreach($needyChildren as $nc){
                $pairs[] = "(".$ag.", ".$nc.")";
            }
            $argList = implode(", ", $pairs);

            $query = "INSERT INTO ".$accessorMetadata->getTableName()." (".$agCol.", ".$division_id") = ".$argList;
        }
        else {
            $query = "DELTE FROM ".$accessorMetadata->getTableName()." WHERE ".$agCol." = ".$entity->getAccessGovernor()->getId()." AND division_id = ".$division_id;
        }

        $conn = $em->getConnection();
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll();

    }

    public function trampleToChildren()
    {

    }

    public function bulkUpdateBooleans()
    {

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

        if (!array_key_exists('propagationBehavior', $request)) {
            return;
        }

        $propMethod = explode(" ",$request['propagationBehavior'])[0];

        if ($propMethod == "Default") {
            return;
        }

        // If this is a creation it will not have an id
            // If it is a creation there is also no need to cascade any permissions
        if (!array_key_exists('id', $request)) {
            return;
        }

        $divisionId = $request['id']; // Listener should only be called when the given division and its accessors are changed.

        if ($propMethod == "Cascade") {

            foreach ($uow->getScheduledEntityInsertions() as $keyEntity => $entity) {

                if ($entity instanceof BaseDivision) {
                    continue;
                }
                elseif ($entity instanceof BaseDivisionAccessGovernor) {
                    $this->cascadeToChildren($em, $divisionId, $entity, true);
                }
            }
            foreach ($uow->getScheduledEntityDeletions() as $keyEntity => $entity) {
                if ($entity instanceof BaseDivision) {
                    continue; // We are not going to allow users to delete divisions that have children -- this case should not take place
                }
                elseif ($entity instanceof BaseDivisionAccessGovernor) {
                    $this->cascadeToChildren($em, $divisionId, $entity, false);
                }
            }
            foreach ($uow->getScheduledEntityUpdates() as $keyEntity => $entity) {

                // This listener should only be called for
                if ($entity instanceof BaseDivision && $entity->getId() == $request['id']) {
                    $divisionMetadata = $em->getClassMetaData(get_class($entity));

                    $accessorBooleans = array();
                    $id = $entity->getId();

                    foreach ( $uow->getEntityChangeset($entity) as $keyField => $field){
                        if ( in_array($keyField, array('isPublicEdit', 'isPublicView', 'allowAllStorageContainers', 'allowAllSampleTypes')) ) {
                            $accessorBooleans[$divisionMetadata->getColumnName($keyField)] = $field[1] ? "true" : "false";
                        }
                    }

                    $childList = $this->buildChildList($conn, $request['id']);
                    $this->bulkUpdateBooleans($conn, $accessorBooleans, $childList);
                }

            }

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

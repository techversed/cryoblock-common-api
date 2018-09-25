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

        $childDivisions = $entity->getDivision()->getChildren();

        foreach($childDivisons as $cd){

            // test if the child division has the same repo available
            $test = new $className();
            //create a new entity

        }


        return true;

    }

    public function removeFromChildren($em, $repo, $entity, $className)
    {

        $childDivisions = $entity->getDivision()->getChildren();

        foreach ($childDivisions as $cd) {

            $test = new $className();

        }

        return true;

    }

    public function updateDivisionBooleans($em, $entity)
    {

        // If work happened here return true
        $childDivisions = $entity->getDivision()->getChildren();

        foreach ($childDivisions as $cd){

            //$em->persist();
        }

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

                $workHappened = $this->updateDivisionBooleans($em, $entity) ? true : $workHappened;

            }

        }


        if($workHappened){
            $em->flush();
        }

    }
}

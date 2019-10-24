<?php

namespace Carbon\ApiBundle\Listener\Storage;

use AppBundle\Entity\Storage\Sample;
use AppBundle\Entity\Storage\Catalog;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\UnitOfWork;
use Symfony\Bridge\Monolog\Logger;
use Carbon\ApiBundle\Entity\Storage\Sequence\BaseSequence;
use Carbon\ApiBundle\Entity\Storage\BaseSample;

/*
    VIOLATION
    This file contains a violation of the idea of Common.
    This should not explicitly move antibody sequences over to the new catalog like this because many implementaitons of this software may not have antibody sequences.

    This violation would be fixed by adding additional properties to entity detail which determine whether or not the new entities should be moved over when a catalog is ranamed...

*/

/*
    We mgiht need to pass int he reques tstack instead of doing it this way --- race conditions and whatnot

    May need to introduce some form of locking here


*/

class CatalogListener
{
    public function onFlush(OnFlushEventArgs $args)
    {
        $em = $args->getEntityManager();
        $conn = $em->getConnection();

        $uow = $em->getUnitOfWork();
        $catalogRepo = $em->getRepository('AppBundle\Entity\Storage\Catalog');
        $sampleRepo = $em->getRepository('AppBundle\Entity\Storage\Sample');

        // Catalog auto naming
        $metadataCatalog = $em->getClassMetadata('AppBundle\Entity\Storage\Catalog');
        $metadataTarget = $em->getClassMetadata('AppBundle\Entity\Storage\Target');

        // When we create a catalog
        // target is not no base sample so this is going to be a bit of a problem

        // This cannot stay in common

        $newCatalogTargets = array();
        $newCatalogs = array();

        foreach ($uow->getScheduledEntityInsertions() as $keyEntity => $entity) {

            // Change this to basesample
            if ($entity instanceof Sample) {

                $explodedCat = explode("+", $entity->getCatalog()->getName());

                if ($explodedCat[0] == "TARGET"){

                    $catId = $entity->getCatalog()->getId();

                    if (!in_array($catId, $newCatalogs)) {

                        $newCatalogs[] = $catId;

                    }

                    if (!array_key_exists($entity->getCatalog()->getId(), $newCatalogTargets)) {

                        $newCatalogTargets = array();

                    }

                    $newCatalogTargets[$entity->getCatalog()->getId()][] = $entity->getTarget();
                }

            }

            // Should be added for sequences also
            // Add in a sequence thing here

        }

        foreach ($newCatalogs as $nc) {

            //
            // $nc->setName()
            $mod = (int) explode("+", $nc->getName())[1];

            foreach (){




            }


        }






        // foreach ($uow->getScheduledEntityInsertions() as $entity) {
        //     if ($entity instanceof Catalog) {

        //         $entity->setName("testing");

        //     }
        // }

        // $catIncrementMax = array();

        // foreach($uow->getScheduledEntityInsertions() as $keyEntity => $entity) {

        //     if ($entity instanceof Catalog) {
        //         $explodedCat = explode("+", $entity->getName());

        //         if (strtoupper($explodedCat[0]) == "TARGET") {


        //             if (count($newCatalogTargets[$entity->getId()]) == 1) {
        //             // if (isset($newCatalogTargets[$entity->getId()]) && count($newCatalogTargets[$entity->getId()]) == 1) {

        //                 $target = $newCatalogTargets[$entity->getId()][0];

        //                 if ((string)((int)$explodedCat[1]) == $explodedCat[1]){

        //                     $add = (int)$explodedCat[1];

        //                     $abb = $target->getAbbreviation();
        //                     $currentMax = $target->getMaxIdUsed() ? $target->getMaxIdUsed() : 1;

        //                     $entity->setName($abb.'-'.(string)($currentMax + $add));

        //                     $catIncrementMax[$target->getId()] = ($catIncrementMax[$target->getId()] < $add) ? $add : $catIncrementMax[$target->getId()];
        //                     $uow->recomputeSingleEntityChangeset($metadataCatalog, $entity);

        //                 }

        //             }

        //         }
        //     }

        // }

        // foreach ($catIncrementMax as $key => $value) {

        //     $target = $targetRepo->find($key);

        //     $target->setMaxIdUsed($target->getMaxIdUsed() + $value);

        //     $uow->recomputeSingleEntityChangeset($metadataTarget, $target);

        // }

        // End of the catalog autonaming portion

        // When updating a catalog to have the same name as an existing catalog it will delete the catalog and move all of its stuff to the new one
        foreach ($uow->getScheduledEntityUpdates() as $keyEntity => $entity) {

            if ($entity instanceof Catalog) {

                $catalogs = $catalogRepo->findBy( array('name' => $entity->getName()) );

                $minId = $entity->getId();
                $catIdList = array();
                $catIdList[] = $entity->getId();

                foreach ($catalogs as $catalog) {

                    $minId = ($minId == 0 ? ($minId < $catalog->getId() ? $minId : $catalog->getId()) : $catalog->getId());

                    $catIdList[] = $catalog->getId();

                }

                $query = $em->createQuery('UPDATE AppBundle\Entity\Storage\Sample s SET s.catalogId = ' . (string) $minId .  ' where s.catalogId in (' .  implode(', ', $catIdList) . ') ');
                $numUpdated = $query->execute();


// This will have to be sorted out using entity detail -- there would have to be a column for whether or not an entity should be moved to a new catalog when the catalog is ranamed
//VIOLATION -- Antibody sequence is not in common --- we will need to find a way to make this more abstract...
                $query = $em->createQuery('UPDATE AppBundle\Entity\Storage\Sequence\Antibody\AntibodySequence s SET s.catalogId = ' . (string) $minId .  ' where s.catalogId in (' .  implode(', ', $catIdList) . ') ');
                $numUpdated = $query->execute();

                $catIdString = array();

                foreach($catIdList as $catId){
                    $catIdString[] = "'" . (string) $catId ."'";
                }

                $query = $em->createQuery('UPDATE Carbon\ApiBundle\Entity\Attachment a SET a.objectId = \'' . (string) $minId . '\' where a.objectId in (' . implode(', ', $catIdString) . ') and a.objectClass = \'AppBundle\Entity\Storage\Catalog\'');
                $catUpdated = $query->execute();

                $now = new \DateTime('now');

                foreach($catIdList as $idEntry){
                    if($idEntry == $minId) continue;

                    $ent = $catalogRepo->find($idEntry);
                    $ent->setName($ent->getId()."->".$minId);
                    $ent->setMergedInto($catalogRepo->find($minId));
                    // $ent->setDeletedAt($now);
                }

            }
        }
    }
}

// This is going to get dropped back into place after we finish creating the samples list
/*

if ($entity instanceof Catalog) {

                echo "found a catalog";
                echo count($entity->getSamples());

                $explodedCat = explode("+", $entity->getName());

                if (strtoupper($explodedCat[0]) == "TARGET") {

                    $targets = array();


                    // echo count($entity->getSamples());

                    // die();

                    // foreach ($entity->getSamples() as $key => $sample) {

                        // if ($sample->getTarget() && !in_array($sample->getTarget(), $targets)) {
                            // $targets[] = $sample->getTarget();
                        // }

                    // }

                    // if (count($targets) == 1) {

                        // $newName = $target->getAbbreviation() . "-" . $target->getMaxIdUsed();

                        $newName = "ballz2";
                        $entity->setName($newName);
                        $uow->recomputeSingleEntityChangeset($metadataCatalog, $entity);

                    // }
                    // else {

                        // foreach ($targets as $target) {


                        // }

                    // }
                }
            }
*/

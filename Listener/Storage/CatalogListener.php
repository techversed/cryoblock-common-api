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
use Doctrine\ORM\Event\PostFlushEventArgs;

/*
    VIOLATION
    This file contains a violation of the idea of Common.
    This should not explicitly move antibody sequences over to the new catalog like this because many implementaitons of this software may not have antibody sequences.

    This violation would be fixed by adding additional properties to entity detail which determine whether or not the new entities should be moved over when a catalog is ranamed...

*/

/*
    We mgiht need to pass int he reques tstack instead of doing it this way --- race conditions and whatnot

    May need to introduce some form of locking here

    VIOLATION -- this stuff that deals directly with samples should not be this listener in common.

    THIS SHOULD REALLY NOT BE IN COMMON...

*/

class CatalogListener
{

    protected $onFlushRan = false;
    protected $targetIncrementAmount = array();
    protected $postFlushRanOnce = false;

    protected $initialMaxUsed = array();

    public function onFlush(OnFlushEventArgs $args)
    {
        $this->onFlushRan = true;
        $em = $args->getEntityManager();
        $conn = $em->getConnection();

        $uow = $em->getUnitOfWork();
        $catalogRepo = $em->getRepository('AppBundle\Entity\Storage\Catalog');
        $sampleRepo = $em->getRepository('AppBundle\Entity\Storage\Sample');
        $targetRepo = $em->getRepository('AppBundle\Entity\Storage\Target');

        // Catalog auto naming
        $metadataCatalog = $em->getClassMetadata('AppBundle\Entity\Storage\Catalog');
        $metadataTarget = $em->getClassMetadata('AppBundle\Entity\Storage\Target');
        $metadataSample = $em->getClassMetadata('AppBundle\Entity\Storage\Sample');

        // When we create a catalog
        // target is not on base sample so this is going to be a bit of a problem

        $newCatalogTargets = array();
        $newCatalogs = array();
        $newCatObjects = array();

        foreach ($uow->getScheduledEntityInsertions() as $keyEntity => $entity) {

            // Change this to basesample
            if ($entity instanceof Sample) {

                $explodedCat = explode("+", $entity->getCatalog()->getName());

                if ($explodedCat[0] == "TARGET"){

                    $catId = $entity->getCatalog()->getId();

                    if (!in_array($catId, $newCatalogs)) {
                        $newCatalogs[] = $catId;
                        $newCatalogObjects[] = $entity->getCatalog();
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

            $cat = $catalogRepo->find($nc);
            $mod = (int) explode("+", $cat->getName())[1];

            $target = $targetRepo->find($newCatalogTargets[$nc][0]);

            if(!isset($this->initialMaxUsed[$target->getId()])){
                $this->initialMaxUsed[$target->getId()] = $target->getMaxIdUsed();// ? $target->getMaxIdUsed() + $mod : 1 + $mod;
            }

            $abbreviationTerm = $target->getAbbreviation();
            $incr = ($this->initialMaxUsed[$target->getId()] +$mod);
            $cat->setName($abbreviationTerm . "-". (string)$incr);

            if(!isset($this->targetIncrementAmount[$target->getId()])){
                $this->targetIncrementAmount[$target->getId()] = $incr;
            }
            else {
                $this->targetIncrementAmount[$target->getId()] = $incr < ($this->targetIncrementAmount[$target->getId()]) ? $this->targetIncrementAmount : $incr;
            }

            $uow->recomputeSingleEntityChangeset($metadataCatalog, $cat);

        } // we can move the commit to outside of this.

        // Add another listener that makes it so that samples that are going to be placed into a catalog with a name collision  will get placed into the catalog
        // This array will store a list of ids and the catalogs [key] and the catalog that they should be merged into [value]
        $catalogFromTo = array();
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

                if ($entity->getSamples() != null) {
                    foreach($entity->getSamples() as $sample) {

                        if(!is_null($entity->getDonor())) {
                            $sample->setDonor($entity->getDonor());
                            $uow->recomputeSingleEntityChangeset($metadataSample, $sample);
                        }

                        if(!is_null($entity->getTarget())) {
                            $sample->setTarget($entity->getTarget());
                            $uow->recomputeSingleEntityChangeset($metadataSample, $sample);
                        }
                    }
                }

                // This call does not work if the catalog is renamed by the earlier thing
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
                $minIdCat = $catalogRepo->find($minId);

                foreach ($catIdList as $idEntry) {
                    if ($idEntry == $minId) continue;

                    $catalogFromTo[$idEntry] = $minIdCat;

                    $ent = $catalogRepo->find($idEntry);
                    $ent->setName($ent->getId()."->".$minId);
                    $ent->setMergedInto($catalogRepo->find($minId));
                }
            }
        }

        $catalogIdList = array();

        foreach ($catalogFromTo as $key => $value) {
            $catalogIdList[] = $key;
        }

        foreach ($uow->getScheduledEntityInsertions() as $keyEntity => $entity) {

            if ($entity instanceof Sample) {
                if (isset($catalogFromTo[$entity->getCatalog()->getId()])) {
                    $entity->setCatalog($catalogFromTo[$entity->getCatalog()->getId()]);
                    $uow->recomputeSingleEntityChangeset($metadataSample, $entity);
                }
            }
        }

        foreach ($this->targetIncrementAmount as $key => $value) {

            $target = $targetRepo->find($key);
            $target->setMaxIdUsed($value);

            $uow->recomputeSingleEntityChangeset($metadataTarget, $target);
        }
    }
}

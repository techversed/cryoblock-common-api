<?php

namespace Carbon\ApiBundle\Listener\Storage;

use AppBundle\Entity\Storage\Sample;
use AppBundle\Entity\Storage\Catalog;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\UnitOfWork;
use Symfony\Bridge\Monolog\Logger;

/*
    VIOLATION
    This file contains a violation of the idea of Common.
    This should not explicitly move antibody sequences over to the new catalog like this because many implementaitons of this software may not have antibody sequences.

    This violation would be fixed by adding additional properties to entity detail which determine whether or not the new entities should be moved over when a catalog is ranamed...




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

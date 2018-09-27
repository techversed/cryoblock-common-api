<?php

namespace Carbon\ApiBundle\Listener\Storage;

use AppBundle\Entity\Storage\Sample;
use AppBundle\Entity\Storage\Catalog;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\UnitOfWork;
use Symfony\Bridge\Monolog\Logger;

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

                    $minId = $minId == 0 ? ($minId < $catalog->getId() ? $minId : $catalog->getId()) : $catalog->getId();

                    $catIdList[] = $catalog->getId();

                }

                $query = $em->createQuery('UPDATE AppBundle\Entity\Storage\Sample s SET s.catalogId = ' . (string) $minId .  ' where s.catalogId in (' .  implode(', ', $catIdList) . ') ');
                $numUpdated = $query->execute();

                $catIdString = array();
                foreach($catIdList as $catId){
                    $catIdString[] = "'" . (string) $catId ."'";
                }

                $query = $em->createQuery('UPDATE Carbon\ApiBundle\Entity\Attachment a SET a.objectId = \'' . (string) $minId . '\' where a.objectId in (' . implode(', ', $catIdString) . ') and a.objectClass = \'AppBundle\Entity\Storage\Catalog\'');
                $catUpdated = $query->execute();

                $query = $em->createQuery('DELETE FROM AppBundle\Entity\Storage\Catalog a WHERE a.id in (' .  implode(', ', $catIdString) . ') AND a.id != '.$minId);
                $catDelete = $query->execute();




            }
        }
    }
}

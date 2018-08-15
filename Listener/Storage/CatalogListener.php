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
        $uow = $em->getUnitOfWork();
        $catalogRepo = $em->getRepository('AppBundle\Entity\Storage\Catalog');
        $sampleRepo = $em->getRepository('AppBundle\Entity\Storage\Sample');

        foreach ($uow->getScheduledEntityUpdates() as $keyEntity => $entity) {

            if ($entity instanceof Catalog) {

                $catalogs = $catalogRepo->findBy( array('name' => $entity->getName()) );

                // $minId = 53337;
                $catIdList = array();
                // $catIdList[] = 53334;

                foreach ($catalogs as $catalog) {

                    $minId = $minId ? ($minId < $catalog->getId() ? $minId : $catalog->getId()) : $catalog->getId();

                    $catIdList[] = $catalog->getId();

                }

                $query = $em->createQuery('UPDATE AppBundle\Entity\Storage\Sample s SET s.catalogId = ' . (string) $minId .  ' where s.catalogId in (' .  implode(', ', $catIdList) . ') ');
                // $query = $em->createQuery('UPDATE AppBundle\Entity\Storage\Sample s SET s.catalogId = 53337 where s.catalogId in (53335)');
                $numUpdated = $query->execute();

                // $em->flush();

            }
        }
    }
}

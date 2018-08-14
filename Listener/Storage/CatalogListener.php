<?php

namespace Carbon\ApiBundle\Listener\Storage;

use AppBundle\Entity\Storage\Sample;
use AppBundle\Entity\Storage\Catalog;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Symfony\Bridge\Monolog\Logger;

class CatalogListener
{
    public function onFlush(OnFlushEventArgs $args)
    {
        $em = $args->getEntityManager();
        $uow = $em->getUnitOfWork();
        $catalogRepo = $em->getRepository('AppBundle\Entity\Storage\Catalog');
        $sampleRepo = $em->getRepository('AppBundle\Entity\Storage\Sample');

        foreach ($uow->getScheduledEntityInsertions() as $keyEntity => $entity) {

            if ($entity instanceof Catalog) {

                $catalogs = $catalogRepo->findBy(array('name' => $entity->getName()));

                $minId = 53337;
                $catIdList = array();

                foreach ($catalogs as $catalog) {

                    // $minId = $minId ? ($minId < $catalog->getId() ? $minId : $catalog->getId()) : $catalog->getId();

                    $catIdList[] = $catalog->getId();

                }

                $query = $em->createQuery('update storage.sample set catalogId = ' . (string) $minId .  ' where catalogId in ' . '(' .  implode(', ', $catIdList) . ')');
                $numUpdated = $query->execute();

                $em->flush();

            }
        }
    }
}

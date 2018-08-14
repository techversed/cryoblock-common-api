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

                $minId = null;
                $catIdList = array();

                foreach ($catalogs as $catalog) {

                    $minId = $minId ? ($minId < $catalog->getId() ? $minId : $catalog->getId()) : $catalog->getId();

                    $catIdList[] = $catalog->getId();

                }

                foreach ($catIdList as $catId) {

                    $query = $em->createQuery('update storage.sample set catalogId = ' + $minId + ' where catalogId in ' + $catIdList);
                    $numUpdated = $query->execute();

                }

                $em->flush();

                // echo count($catalogRepo);


                // $query = $em->createQuery('select * from storage.sample ')

                // $sampleName = $entity->getName();
                // $catalog = $catalogRepo->findOneByName($sampleName);

                // if (!$catalog) {
                //     $catalog = new Catalog();
                //     $catalog->setName($sampleName);
                //     $catalog->setStatus('Available');
                //     $uow->persist($catalog);
                //     $metaCatalog = $em->getClassMetadata(get_class($catalog));
                //     $uow->computeChangeSet($metaCatalog, $catalog);
                // }

            }

        }
    }
}

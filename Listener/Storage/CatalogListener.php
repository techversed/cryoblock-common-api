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

        foreach ($uow->getScheduledEntityInsertions() as $keyEntity => $entity) {

            if ($entity instanceof Sample) {

                $sampleName = $entity->getName();
                $catalog = $catalogRepo->findOneByName($sampleName);

                if (!$catalog) {
                    $catalog = new Catalog();
                    $catalog->setName($sampleName);
                    $catalog->setStatus('Available');
                    $uow->persist($catalog);
                    $metaCatalog = $em->getClassMetadata(get_class($catalog));
                    $uow->computeChangeSet($metaCatalog, $catalog);
                }

            }

        }
    }
}

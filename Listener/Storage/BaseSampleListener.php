<?php

namespace Carbon\ApiBundle\Listener\Storage;

use AppBundle\Entity\Storage\Division;
use AppBundle\Entity\Storage\Sample;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\UnitOfWork;
use Symfony\Bridge\Monolog\Logger;

class BaseSampleListener
{
    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
        $this->count = 0;
    }

    public function onFlush(OnFlushEventArgs $args)
    {
        $em = $args->getEntityManager();
        $uow = $em->getUnitOfWork();
        $divisionsToUpdate = [];

        foreach ($uow->getScheduledEntityInsertions() as $keyEntity => $entity) {

            if ($entity instanceof Sample) {

                $this->computeDivisionChangeSet($uow, $entity, $divisionsToUpdate);

            }

            if ($entity instanceof Division) {

                $this->setDivisionStats($entity);
                $this->setDivisionPath($entity);

                $metaDivision = $em->getClassMetadata(get_class($entity));
                $uow->recomputeSingleEntityChangeSet($metaDivision, $entity);

            }

        }

        foreach ($uow->getScheduledEntityUpdates() as $keyEntity => $entity) {

            if ($entity instanceof Sample) {

                $this->computeDivisionChangeSet($uow, $entity, $divisionsToUpdate);

            }

            if ($entity instanceof Division) {

                $this->setDivisionPath($entity);

                $metaDivision = $em->getClassMetadata(get_class($entity));
                $uow->recomputeSingleEntityChangeSet($metaDivision, $entity);

            }

        }

        foreach ($divisionsToUpdate as $divisionId => $map) {

            $division = $map['division'];

            $this->updateDivision($division, $map['removeCount'], $map['addCount']);

            $metaDivision = $em->getClassMetadata(get_class($division));
            $uow->computeChangeSet($metaDivision, $division);

        }

    }

    private function computeDivisionChangeSet(UnitOfWork $uow, $entity, &$divisionsToUpdate)
    {
        foreach ($uow->getEntityChangeSet($entity) as $keyField => $field) {

            if ($keyField === 'status') {
                $oldStatus = $field[0];
                $newStatus = $field[1];

                if (
                    $newStatus === 'Depleted' ||
                    $newStatus === 'Destroyed' ||
                    $newStatus === 'Shipped'
                )
                {
                    $oldDivision = $entity->getDivision();
                    if ($oldDivision) {
                        $oldDivisionId = $oldDivision->getId();
                        if (!isset($divisionsToUpdate[$oldDivisionId])) {
                            $divisionsToUpdate[$oldDivisionId] = array(
                                'division' => $oldDivision,
                                'removeCount' => 0,
                                'addCount' => 0
                            );
                        }
                        $divisionsToUpdate[$oldDivisionId]['removeCount']++;
                    }

                    $entity->setDivision(null);
                    $entity->setDivisionId(null);
                    $entity->setDivisionRow(null);
                    $entity->setDivisionColumn(null);
                }
            }

            if ($keyField === 'division') {

                $oldDivision = $field[0];
                $newDivision = $field[1];

                if ($oldDivision) {
                    $oldDivisionId = $oldDivision->getId();
                    if (!isset($divisionsToUpdate[$oldDivisionId])) {
                        $divisionsToUpdate[$oldDivisionId] = array(
                            'division' => $oldDivision,
                            'removeCount' => 0,
                            'addCount' => 0
                        );
                    }
                    $divisionsToUpdate[$oldDivisionId]['removeCount']++;
                }

                if ($newDivision) {
                    $newDivisionId = $newDivision->getId();
                    if (!isset($divisionsToUpdate[$newDivisionId])) {
                        $divisionsToUpdate[$newDivisionId] = array(
                            'division' => $newDivision,
                            'removeCount' => 0,
                            'addCount' => 0
                        );
                    }
                    $divisionsToUpdate[$newDivisionId]['addCount']++;
                }

            }
        }
    }

    private function updateDivision(Division $division, $removeCount, $addCount)
    {
        if (!$division->getHasDimension()) {
            return;
        }

        $height = $division->getHeight();
        $width = $division->getWidth();

        $totalSamples = count($division->getSamples()) - $removeCount + $addCount;
        $totalSlots = $height * $width;
        $availableSlots = $totalSlots - $totalSamples;

        $division->setTotalSlots($totalSlots);
        $division->setUsedSlots($totalSamples);
        $division->setAvailableSlots($totalSlots - $totalSamples);
        $division->setPercentFull(($totalSamples / $totalSlots) * 100);
    }

    private function setDivisionStats(Division $division)
    {
        if ($division->getHasDimension()) {

            $height = $division->getHeight();
            $width = $division->getWidth();
            $totalSlots = $height * $width;

            $division->setTotalSlots($totalSlots);
            $division->setUsedSlots(0);
            $division->setPercentFull(0);
            $division->setAvailableSlots($totalSlots);

        }
    }

    private function setDivisionPath(Division $division)
    {
        $tree = array();
        $tree[] = $currentDivision = $division;

        while ($currentDivision) {

            $currentDivision = $currentDivision->getParent();

            if ($currentDivision) {
                $tree[] = $currentDivision;
            }

        }

        $path = array();
        $idPath = array();
        $tree = array_reverse($tree);

        unset($tree[0]);

        foreach ($tree as $node) {
            $path[] = $node->getTitle();
            $idPath[] = $node->getId();
        }

        $path = implode(' / ', $path);
        $idPath = ' ' . implode(' ', $idPath) . ' ';

        $division->setPath($path);
        $division->setIdPath($idPath);
    }
}

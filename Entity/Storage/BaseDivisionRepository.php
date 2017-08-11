<?php

namespace Carbon\ApiBundle\Entity\Storage;

use AppBundle\Entity\Storage\Division;
use AppBundle\Entity\Storage\SampleType;
use AppBundle\Entity\Storage\StorageContainer;
use Carbon\ApiBundle\Entity\User;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;

class BaseDivisionRepository extends NestedTreeRepository
{
    public function findMatchedDivisionsWithDimension(SampleType $sampleType, StorageContainer $storageContainer)
    {
        $qb = $this->createQueryBuilder('d');

        $qb
            ->innerJoin('d.divisionSampleTypes', 'dst')
            ->innerJoin('d.divisionStorageContainers', 'dsc')
            ->andWhere('d.hasDimension = TRUE')
            ->andWhere('dst.sampleTypeId = :sampleTypeId')
            ->andWhere('dsc.storageContainerId = :storageContainerId')
            ->setParameter('sampleTypeId', $sampleType->getId())
            ->setParameter('storageContainerId', $storageContainer->getId())
        ;

        return $qb->getQuery()->getResult();
    }

    public function findMatchedDimensionlessDivisions(SampleType $sampleType, StorageContainer $storageContainer)
    {
        $qb = $this->createQueryBuilder('d');

        $qb
            ->innerJoin('d.divisionSampleTypes', 'dst')
            ->innerJoin('d.divisionStorageContainers', 'dsc')
            ->andWhere('d.hasDimension = FALSE')
            ->andWhere('dst.sampleTypeId = :sampleTypeId')
            ->andWhere('dsc.storageContainerId = :storageContainerId')
            ->setParameter('sampleTypeId', $sampleType->getId())
            ->setParameter('storageContainerId', $storageContainer->getId())
        ;

        return $qb->getQuery()->getResult();
    }

    public function buildMatchQuery($sampleTypeId, $storageContainerId, User $user)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();

        $qb->select(array('d'))
            ->from('AppBundle\Entity\Storage\Division', 'd')

            ->leftJoin('AppBundle\Entity\Storage\DivisionSampleType', 'dsd', Join::WITH, 'dsd.divisionId = d.id')
            ->leftJoin('AppBundle\Entity\Storage\DivisionStorageContainer', 'dsc', Join::WITH, 'dsc.divisionId = d.id')
            ->leftJoin('AppBundle\Entity\Storage\DivisionEditor', 'de', Join::WITH, 'de.divisionId = d.id')
        ;

        $qb->andWhere($qb->expr()->orX(
            $qb->expr()->eq('dsd.sampleTypeId ', $sampleTypeId),
            $qb->expr()->eq('d.allowAllSampleTypes', 'true')
        ));

        $qb->andWhere($qb->expr()->orX(
            $qb->expr()->eq('dsc.storageContainerId ', $storageContainerId),
            $qb->expr()->eq('d.allowAllStorageContainers', 'true')
        ));

        $qb->andWhere('d.percentFull < 100');

        if (!$user->hasRole('ROLE_ADMIN') && !$user->hasRole('ROLE_INVENTORY_ADMIN')) {

            $sub = $this->getEntityManager()->createQueryBuilder();

            $sub
                ->select('dge2')
                ->from('AppBundle\\Entity\\Storage\\DivisionGroupEditor', 'dge2')
                ->innerJoin('Carbon\\ApiBundle\\Entity\\UserGroup', 'ug', Join::WITH, 'dge2.groupId = ug.groupId')
                ->andWhere('dge2.divisionId = d.id')
                ->andWhere('ug.userId = ' . $user->getId())
            ;

            $qb->andWhere($qb->expr()->orX(
                $qb->expr()->exists($sub->getDQL()),
                $qb->expr()->eq('de.userId', $user->getId()),
                $qb->expr()->eq('d.isPublicEdit', 'true')
            ));

        }

        $qb->orderBy('d.percentFull', 'DESC');

        return $qb;
    }

    public function getAvailableCells(Division $division)
    {
        $width = $division->getWidth();
        $height = $division->getHeight();
        $alphabet = range('A', 'Z');

        $divisionSamples = $division->getSamples();

        $currentInventoryMap = array();
        foreach ($divisionSamples as $divisionSample) {
            $currentInventoryMap[$divisionSample->getDivisionRow()][$divisionSample->getDivisionColumn()] = true;
        }

        $rows = range('A', $alphabet[$height - 1]);
        $emptyLocations = array();
        foreach ($rows as $row) {

            foreach (range(1, $width) as $column) {

                if (!isset($currentInventoryMap[$row][$column])) {
                    if (!isset($emptyLocations[$row])) {
                        $emptyLocations[$row] = array();
                    }

                    $emptyLocations[$row][$column] = true;

                }

            }
        }

        return $emptyLocations;
    }

    public function canUserView(Division $division, User $user)
    {
        if ($division->getIsPublicEdit()) {
            return true;
        }

        if ($this->canUserEdit($division, $user)) {
            return true;
        }

        if ($division->getIsPublicView()) {
            return true;
        }

        if ($user->hasRole('ROLE_ADMIN')) {
            return true;
        }

        # check for view groups
        $viewGroups = $this->getEntityManager()->createQueryBuilder()
            ->select('dgv')
            ->from('AppBundle\\Entity\\Storage\\DivisionGroupViewer', 'dgv')
            ->innerJoin('Carbon\\ApiBundle\\Entity\\UserGroup', 'ug', Join::WITH, 'dgv.groupId = ug.groupId')
            ->andWhere('dgv.divisionId = ' . $division->getId())
            ->andWhere('ug.userId = ' . $user->getId())
            ->getQuery()
            ->getResult()
        ;

        if (count($viewGroups)) {
            return true;
        }

        $qb = $this->getEntityManager()->createQueryBuilder();

        $result = $qb->select(array('dv'))

            ->from('AppBundle\Entity\Storage\DivisionViewer', 'dv')

            ->where('dv.userId = :userId')
            ->andWhere('dv.divisionId = :divisionId')

            ->setParameter('userId', $user->getId())
            ->setParameter('divisionId', $division->getId())

            ->getQuery()
            ->getResult()
        ;

        return count($result) == 1;
    }

    public function canUserEdit(Division $division, User $user)
    {
        if ($division->getIsPublicEdit()) {
            return true;
        }

        if ($user->hasRole('ROLE_ADMIN')) {
            return true;
        }

        # check for view groups
        $editGroups = $this->getEntityManager()->createQueryBuilder()
            ->select('dge')
            ->from('AppBundle\\Entity\\Storage\\DivisionGroupEditor', 'dge')
            ->innerJoin('Carbon\\ApiBundle\\Entity\\UserGroup', 'ug', Join::WITH, 'dge.groupId = ug.groupId')
            ->andWhere('dge.divisionId = ' . $division->getId())
            ->andWhere('ug.userId = ' . $user->getId())
            ->getQuery()
            ->getResult()
        ;

        if (count($editGroups)) {
            return true;
        }

        $qb = $this->getEntityManager()->createQueryBuilder();

        $result = $qb->select(array('de'))

            ->from('AppBundle\Entity\Storage\DivisionEditor', 'de')

            ->where('de.userId = :userId')
            ->andWhere('de.divisionId = :divisionId')

            ->setParameter('userId', $user->getId())
            ->setParameter('divisionId', $division->getId())

            ->getQuery()
            ->getResult()
        ;

        return count($result) == 1;
    }
}
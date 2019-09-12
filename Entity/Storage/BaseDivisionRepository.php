<?php

namespace Carbon\ApiBundle\Entity\Storage;

use AppBundle\Entity\Storage\Division;
use AppBundle\Entity\Storage\SampleType;
use AppBundle\Entity\Storage\StorageContainer;
use Carbon\ApiBundle\Entity\User;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;

/*

    Future changes that will need to take place here
    There are changes that will need to be made to buildMatchQuery in order to make it so that the count of valid matching divisions which are returned are equal to the number of entities which should show up in the results set if we are going strictly baseed upon filter.
        --Remembered what the previosus comment was about -- If permissions are set then the user can receive a list where several elements have been removed from suggestion lists
        --If a user requests a list of 25 top storage locations then they can run into issues whre 25 are returned to the entity manager but then the non-applicable ones are filtered out -- the end result is that fewer than 25 elements are reutnred to the user.

*/

class BaseDivisionRepository extends NestedTreeRepository
{
    /*


    */
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

    /*



    */
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


    /*


    */
    // Changes needed here.
    // We are also ordering by percentfull? what about matches?
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

        if (!$user->hasRole('ROLE_INVENTORY_ADMIN')) {

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

    /*



    */
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

    /*
        Determines if a user can view a given division

        Takes a division object and a user object
        Returns a boolean which is true in the event that the supplied user is able to edit the division and false if they are not able to

    */
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

        if ($user->hasRole('ROLE_INVENTORY_ADMIN')) {
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

    /*

        checks if a user can edit a given division

        takes a division and a user as arguments
        returns a boolean which is true in the event that they can edit and false in the event that they cannot edit

    */
    public function canUserEdit(Division $division, User $user)
    {
        if ($division->getIsPublicEdit()) {
            return true;
        }

        if ($user->hasRole('ROLE_INVENTORY_ADMIN')) {
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

    /*
        Checks to see if a sample of a given sampletype in a given storage container can be placed in a given division
    */
    public function allowsSamplePlacement(Division $division, SampleType $sampleType, StorageContainer $storageContainer)
    {

        // echo $division->getId();

        $qb = $this->createQueryBuilder('d');

        $result = $qb
            ->leftJoin('d.divisionSampleTypes', 'dst')
            ->leftJoin('d.divisionStorageContainers', 'dsc')
            ->andWhere('d.id = :divisionId')
            ->andWhere($qb->expr()->orX(
                $qb->expr()->eq('dst.sampleTypeId', $sampleType->getId()),
                $qb->expr()->eq('d.allowAllSampleTypes', 'true')
            ))
            ->andWhere($qb->expr()->orX(
                $qb->expr()->eq('dsc.storageContainerId', $storageContainer->getId()),
                $qb->expr()->eq('d.allowAllStorageContainers', 'true')
            ))
            ->setParameter('divisionId', $division->getId())
            ->getQuery()
            ->getResult()
        ;

        return count($result) >= 1;

    }





}

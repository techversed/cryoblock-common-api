<?php

namespace Carbon\ApiBundle\Entity\Help;

use AppBundle\Entity\Help\Help;
use Carbon\ApiBundle\Entity\User;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;

class BaseHelpRepository extends NestedTreeRepository
{
    public function buildMatchQuery(User $user)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();

        $qb->select(array('d'))
            ->from('AppBundle\Entity\Help\Help', 'd')

            ->leftJoin('AppBundle\Entity\Help\HelpEditor', 'de', Join::WITH, 'de.helpId = d.id')
        ;

        if (!$user->hasRole('ROLE_HELP_ADMIN')) {

            $sub = $this->getEntityManager()->createQueryBuilder();

            $sub
                ->select('dge2')
                ->from('AppBundle\\Entity\\Help\\HelpGroupEditor', 'dge2')
                ->innerJoin('Carbon\\ApiBundle\\Entity\\UserGroup', 'ug', Join::WITH, 'dge2.groupId = ug.groupId')
                ->andWhere('dge2.helpId = d.id')
                ->andWhere('ug.userId = ' . $user->getId())
            ;

            $qb->andWhere($qb->expr()->orX(
                $qb->expr()->eq('de.userId', $user->getId()),
                $qb->expr()->eq('d.isPublicEdit', 'true')
            ));

        }

        $qb->orderBy('d.id', 'DESC');

        return $qb;
    }

    public function canUserView(Help $help, User $user)
    {
        if ($help->getIsPublicEdit()) {
            return true;
        }

        if ($this->canUserEdit($help, $user)) {
            return true;
        }

        if ($help->getIsPublicView()) {
            return true;
        }

        if ($user->hasRole('ROLE_HELP_ADMIN')) {
            return true;
        }

        # check for view groups
        $viewGroups = $this->getEntityManager()->createQueryBuilder()
            ->select('dgv')
            ->from('AppBundle\\Entity\\Help\\HelpGroupViewer', 'dgv')
            ->innerJoin('Carbon\\ApiBundle\\Entity\\UserGroup', 'ug', Join::WITH, 'dgv.groupId = ug.groupId')
            ->andWhere('dgv.helpId = ' . $help->getId())
            ->andWhere('ug.userId = ' . $user->getId())
            ->getQuery()
            ->getResult()
        ;

        if (count($viewGroups)) {
            return true;
        }

        $qb = $this->getEntityManager()->createQueryBuilder();

        $result = $qb->select(array('dv'))

            ->from('AppBundle\Entity\Help\HelpViewer', 'dv')

            ->where('dv.userId = :userId')
            ->andWhere('dv.helpId = :helpId')

            ->setParameter('userId', $user->getId())
            ->setParameter('helpId', $help->getId())

            ->getQuery()
            ->getResult()
        ;

        return count($result) == 1;
    }

    public function canUserEdit(Help $help, User $user)
    {
        if ($help->getIsPublicEdit()) {
            return true;
        }

        if ($user->hasRole('ROLE_HELP_ADMIN')) {
            return true;
        }

        # check for view groups
        $editGroups = $this->getEntityManager()->createQueryBuilder()
            ->select('dge')
            ->from('AppBundle\\Entity\\Help\\HelpGroupEditor', 'dge')
            ->innerJoin('Carbon\\ApiBundle\\Entity\\UserGroup', 'ug', Join::WITH, 'dge.groupId = ug.groupId')
            ->andWhere('dge.helpId = ' . $help->getId())
            ->andWhere('ug.userId = ' . $user->getId())
            ->getQuery()
            ->getResult()
        ;

        if (count($editGroups)) {
            return true;
        }

        $qb = $this->getEntityManager()->createQueryBuilder();

        $result = $qb->select(array('de'))

            ->from('AppBundle\Entity\Help\HelpEditor', 'de')

            ->where('de.userId = :userId')
            ->andWhere('de.helpId = :helpId')

            ->setParameter('userId', $user->getId())
            ->setParameter('helpId', $help->getId())

            ->getQuery()
            ->getResult()
        ;

        return count($result) == 1;
    }
}

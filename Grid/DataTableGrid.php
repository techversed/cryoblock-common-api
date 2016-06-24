<?php

namespace Carbon\ApiBundle\Grid;

use Doctrine\ORM\EntityRepository;

/**
 * Data table grid used with jquery data table integration
 */
class DataTableGrid extends Grid
{
    /**
     * Default handling of jquery data tables filter requests
     *
     * @param  EntityRepository $repo
     * @return array
     */
    public function getResult(EntityRepository $repo)
    {
        $request = $this->request;

        $columns = $request->get('columns');
        $search = $request->get('search');
        $start = $request->get('start');
        $length = $request->get('length');
        $order = $request->get('order');
        $orderColumn = $columns[$order[0]['column']]['data'];
        $orderDir = $order[0]['dir'];

        $searchableColumns = array_filter($columns, function ($column) {
            return $column['searchable'] == 'true';
        });

        $qb = $repo->createQueryBuilder($alias = 'a');

        if ($search['value'] !== '') {

            foreach ($searchableColumns as $column) {
                $paramName = 'LIKE_'.$column['data'];
                $searchExpressions[] = sprintf('%s.%s LIKE :%s', $alias, $column['data'], $paramName);
                $qb->setParameter($paramName, '%'.$search['value'].'%');
            }

            $qb->andWhere(implode(' OR ', $searchExpressions));

        }

        $qb->orderBy(sprintf('%s.%s', $alias, $orderColumn), $orderDir);

        $recordsTotal = count($qb->getQuery()->getResult());

        $qb
            ->setFirstResult($start)
            ->setMaxResults($length)
        ;

        $result = $qb->getQuery()->getResult();

        $responseData = array(
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsTotal,
            'data' => $result,
        );

        return $responseData;
    }
}

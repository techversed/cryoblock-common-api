<?php

namespace Carbon\ApiBundle\Grid;

use Doctrine\ORM\EntityRepository;

/**
 * The CarbonGrid is used to aid in building paginated
 * API responses when querying for a resource. The default
 * getResult method takes your entites repository class
 * and builds a query based on the get parameters
 * sent from the request. See Carbon\ApiBundle\Grid\Grid for
 * the query params that must be sent for pagination.
 *
 * If you're not overriding the default query, use the
 * @Searchable annotation on the entity properties you want to
 * include in the like string search.
 *
 * @author Andre Jon Branchizio <andrejbranch@gmail.com>
 */
class CarbonGrid extends Grid
{
    /**
     * The default grid query for a entity. Extend
     * this class in your own grid and override this
     * method if you need to define a more complicated
     * query. i.e. a query including searching joined
     * columns
     *
     * @param  EntityRepository $repo
     * @return array
     */
    public function getResult(EntityRepository $repo)
    {
        $qb = $repo->createQueryBuilder($alias = 'a');

        return $this->handleQueryFilters($qb, $alias, $repo->getClassName());
    }

    public function handleQueryFilters($qb, $alias, $className)
    {
        $queryParams = $this->getQueryParams();

        foreach ($queryParams as $k => $v) {

            if (array_key_exists('in', $v)) {

                $qb->andWhere($qb->expr()->in(
                    $alias . '.' . $k,
                    $v['in']
                ));

            }

            if (array_key_exists('GTE', $v)) {

                $qb->andWhere(sprintf('%s.%s >= :%sGTE', $alias, $k, $k))
                    ->setParameter($k . 'GTE', $v['GTE'], 'decimal')
                ;

            }

            if (array_key_exists('LTE', $v)) {

                $qb->andWhere(sprintf('%s.%s <= :%sLTE', $alias, $k, $k))
                    ->setParameter($k . 'LTE', $v['LTE'], 'decimal')
                ;

            }

            if (array_key_exists('EQ', $v)) {

                $qb->andWhere(sprintf('%s.%s = :%sEQ', $alias, $k, $k))
                    ->setParameter($k . 'EQ', $v['EQ'], 'decimal')
                ;

            }

            if (array_key_exists('IN', $v)) {

                $qb->andWhere($qb->expr()->in(
                    $alias . '.' . $k,
                    $v['IN']
                ));

            }

            if (array_key_exists('NULL', $v)) {

                if ((bool) $v['NULL']) {
                    $qb->andWhere(sprintf('%s.%s IS NULL', $alias, $k));
                }

            }

            // foreach ($v['in'] as $in) {
            //     var_dump($in);
            // }
            // if (is_array($v)) {

            //     $qb->andWhere($qb->expr()->in(
            //         $alias . '.' . $k,
            //         $v
            //     ));

            // } else {

            //     if (strtolower($v) === 'null') {

            //         $qb->andWhere(sprintf('%s.%s IS NULL', $alias, $k));

            //     } else {

            //         $qb
            //             ->andWhere(sprintf('%s.%s = :%s', $alias, $k, $k))
            //             ->setParameter($k, $v)
            //         ;

            //     }

            // }

        }

        if ($filteredValueMap = $this->getFilteredValueMap()) {

            foreach ($filteredValueMap as $prop => $filteredValues) {

                $param = sprintf('%s_not_in', $prop);

                $qb
                    ->andWhere($qb->expr()->notIn(
                        $alias . '.' . $prop,
                        $filteredValues
                    ))
                ;

            }

        }

        // If we have a search string sent in the request header
        // add LIKE search expressions for the entity properties
        // with the searchable annotation, then add LIKE search
        // expressions to the query
        if ($likeSearch = $this->getLikeSearchString()) {

            $searchExpressions = array();

            $searchableColumns = $this->annotationReader->getSearchableColumns($className);

            if (count($searchableColumns) === 0) {
                throw new \RunTimeException(sprintf(
                    "No searchable properties are set on entity %s,
                    did you forget to add the @Searchable annotation
                    on the properties you want to search?",
                   $className
                ));
            }

            foreach ($searchableColumns as $columnName) {
                $paramName = 'LIKE_'.$columnName;
                $searchExpressions[] = sprintf('%s.%s LIKE :%s', $alias, $columnName, $paramName);
                $qb->setParameter($paramName, $likeSearch);
            }

            $qb->andWhere(implode(' OR ', $searchExpressions));

        }

        if ($this->shouldShowDeleted()) {
            $filter = $this->em->getFilters()->enable('softdeleteable');
            $filter->disableForEntity($className);
        }

        if ($orderBy = $this->getOrderBy()) {
            $qb->orderBy(sprintf('%s.%s', $alias, $orderBy[0]), $orderBy[1]);
        }

        // used for for pagination to see how many total results there are
        // before limit and offset
        $this->setUnpaginatedTotal(count($qb->getQuery()->getResult()));

        $qb
            ->setFirstResult($this->getOffset())
            ->setMaxResults($this->getPerPage())
        ;

        $result = $qb->getQuery()->getResult();

        return $this->buildGridResponse($result);

    }

    /**
     * Extract only model related parameters from the request
     *
     * @return array
     */
    protected function getQueryParams()
    {
        return array_diff_key($this->request->query->all(), array_flip($this->validGridQueryParams));
    }
}

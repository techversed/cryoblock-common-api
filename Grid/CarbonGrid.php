<?php

namespace Carbon\ApiBundle\Grid;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\ORM\Query\Expr\Join;

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

                if (is_array($v['EQ'])) {

                    $qb->andWhere($qb->expr()->in(
                        $alias . '.' . $k,
                        $v['EQ']
                    ));

                } else {

                    $qb->andWhere(sprintf('%s.%s = :%sEQ', $alias, $k, $k))
                        ->setParameter($k . 'EQ', $v['EQ'], 'decimal')
                    ;

                }

            }

            if (array_key_exists('IN', $v)) {

                if (!is_array($v['IN'])) {
                    $v['IN'] = explode(',', $v['IN']);
                }

                # is this mtm
                if (strpos($k, '_')) {

                    $mtmParams = explode("_", $k);
                    $mtmAlias = substr(str_shuffle("ABCDEFGHIJKLMNOPQRSTUVWXYZ"), -5);

                    $qb->innerJoin($alias . '.' . $mtmParams[0], $mtmAlias, 'WITH', $mtmAlias . '.' . $mtmParams[1] . ' IN (' . implode(',', $v['IN']) . ')' );

                } else {

                    $qb->andWhere($qb->expr()->in(
                        $alias . '.' . $k,
                        $v['IN']
                    ));

                }

            }

            if (array_key_exists('NULL', $v)) {

                if ((bool) $v['NULL']) {
                    $qb->andWhere(sprintf('%s.%s IS NULL', $alias, $k));
                }

            }

            if (array_key_exists('EXISTS', $v)) {

                if ((bool) $v['EXISTS']) {
                    $qb->andWhere(sprintf('%s.%s IS NOT NULL', $alias, $k));
                }

            }

            if (array_key_exists('LIKE', $v)) {

                $qb->andWhere(sprintf('lower(%s.%s) LIKE lower(:%sLIKE)', $alias, $k, $k))
                    ->setParameter($k . 'LIKE', '%' . str_replace(' ', '%', $v['LIKE']) . '%')
                ;

            }

            if (array_key_exists('NE', $v)) {

                $qb
                    ->andWhere($qb->expr()->notIn(
                        $alias . '.' . $k,
                        $v['NE']
                    ))
                ;

            }


            // if ($filteredValueMap = $this->getFilteredValueMap()) {

            //     foreach ($filteredValueMap as $prop => $filteredValues) {

            //         $param = sprintf('%s_not_in', $prop);

            //         $qb
            //             ->andWhere($qb->expr()->notIn(
            //                 $alias . '.' . $prop,
            //                 $filteredValues
            //             ))
            //         ;

            //     }

            // }

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


            foreach ($searchableColumns as $searchableAnnotation) {

                $columnName = $searchableAnnotation->name;

                if ($searchableAnnotation->join) {

                    $subAlias = $searchableAnnotation->subAlias;
                    $joinProp = $searchableAnnotation->joinProp;
                    $meta = $this->em->getClassMetaData($className)->getAssociationMapping($columnName);
                    $joinColumn = $meta['joinColumns'][0]['name'];
                    $referencedColumnName = $meta['joinColumns'][0]['referencedColumnName'];
                    $targetEntity = $meta['targetEntity'];
                    $qb->innerJoin($targetEntity, $subAlias, Join::WITH, sprintf('%s.%s = %s.%s', $subAlias, $referencedColumnName, $alias, 'catalogId'));

                    if ($searchableAnnotation->int) {
                        if (is_numeric($this->getQueryParam(self::QUERY_LIKE_SEARCH))) {
                            $searchExpressions[] = sprintf('%s.%s = %s', $subAlias, $joinProp, $this->getQueryParam(self::QUERY_LIKE_SEARCH));
                        }
                    } else {
                        $searchExpressions[] = sprintf('%s.%s LIKE \'%s\'', $subAlias, $joinProp, $likeSearch);
                    }


                } else {

                    if ($searchableAnnotation->int) {

                        if (is_numeric($this->getQueryParam(self::QUERY_LIKE_SEARCH))) {
                            $searchExpressions[] = sprintf('%s.%s = %s', $alias, $columnName, $this->getQueryParam(self::QUERY_LIKE_SEARCH));
                        }

                    } else {

                        $paramName = 'LIKE_'.$columnName;
                        $searchExpressions[] = sprintf('lower(%s.%s) LIKE lower(:%s)', $alias, $columnName, $paramName);
                        $qb->setParameter($paramName, $likeSearch);

                    }

                }

            }

            $qb->andWhere(implode(' OR ', $searchExpressions));

        }

        if ($this->shouldShowDeleted()) {
            $filter = $this->em->getFilters()->enable('softdeleteable');
            $filter->disableForEntity($className);
        }

        // used for for pagination to see how many total results there are
        // before limit and offset
        $countQb = clone $qb;
        $countQb->select('COUNT(' . $alias . ')');
        $countQb->resetDQLPart('orderBy');

        $this->setUnpaginatedTotal($countQb->getQuery()->getSingleScalarResult());

        if ($orderBy = $this->getOrderBy()) {
            $qb->orderBy(sprintf('%s.%s', $alias, $orderBy[0]), $orderBy[1]);
        }

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
    public function getQueryParams()
    {
        return array_diff_key($this->request->query->all(), array_flip($this->validGridQueryParams));
    }
}

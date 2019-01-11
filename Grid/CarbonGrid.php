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

/*
    Things that will need to be fixed / built in the near future.

    MTM searching
        -There are currently some complications associated with mtm searching with linker tables -- these will need to be worked out soon.

    Layering Violation
        -The MTM searching implementation that I am building reaches around the entity manager and goes directly to the database -- this is a huge layering violation that should be fixed in future
        -This assumes that they are using an sql based database which limts the databse choice of implementations to a smaller set than what is supported by Doctrine.

    Generate Excel sheet function
        -We should make it so that you are able to generate an excel page of the full results of any grid. -- there might need to be a limit in place regarding the max number of results that can be in the grid due to the fact that there is probably a point at which the memory requirements would result in the failure of the query.
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
        }

        // If we have a search string sent in the request header
        // add LIKE search expressions for the entity properties
        // with the searchable annotation, then add LIKE search
        // expressions to the query
        if ($likeSearch = $this->getLikeSearchString()) {

            $searchExpressions = array();

            $searchableColumns = $this->annotationReader->getSearchableColumns($className);
            echo implode($searchableColumns);
            die();

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

/* Changes made here to support mtm linking ... */
                if ($searchableAnnotation->linkerSearch) {

                    // Remove this printing once we are done putting this together.
                    echo $className;
                    echo "<br />";
                    echo $columnName;
                    echo "<br />";

                    $meta = $this->em->getClassMetaData($className)->getAssociationMapping($columnName);

                    echo implode(array_keys($meta));
                    echo "<br />";

                    // $targetEntity = $meta['targetEntity']; // Linker table
                    $linkerTableClassName = $meta['targetEntity']; // Linker table
                    $linkerOppositeAttribute = $searchableAnnotation->;

                    // $linkerTableClassName = $meta['linkerRepo'];
                    // $linkerTableMappedBy = $meta['mappedBy']; // Linker table mapped by should be the object on the Linker table which points back to the entity who's table is being search with the query that is currently being executed.
                    // create meta2 which gathers properties from the linker table...
                    //THIS LINE WILL NEED TO BE UNCOMMENTED AT SOME POINT
                    // $linkerTableMeta = $this->getClassMetaData($linkerTableClassName)->getAssociationMapping($ballz);

                    $subAlias = $searchableAnnotation->subAlias;

                    // We should really handle this by just querying the database directly instead of trying to handle it with round about joins -- this is bullshit.

                    // $searchProp = $searchableAnnotation->searchProp;
                    // $joinProp = $searchableAnnotation->joinProp;
                    // Add a count to the record and filter for things that have counts greater than 1

                    continue;
                    die();
                    // REMOVE THIS;

                    // If id is in the list of ids that are linked with an object that matches the query that we have given.
                    // Append to search expressions.

                    //This will be used in order to
                    $meta = $this->em->getClassMetaData($className)->getAssociationMapping($columnName);

                    //I think this is easiest if I just avoid spawning a second query builder and instead just
                    $ids = array();
                    // foreach

                    $query = "";
                    $conn = $this->em->getConnection();
                    $stmt = $conn->prepare($query);
                    $stmt->execute();

                    foreach ($stmt->fetchAll() as $linkedEntities) {


                    }
/* END OF MTM CHANGES */

                } elseif ($searchableAnnotation->join) {

                    $subAlias = $searchableAnnotation->subAlias;
                    $searchProp = $searchableAnnotation->searchProp;
                    $joinProp = $searchableAnnotation->joinProp;
                    $meta = $this->em->getClassMetaData($className)->getAssociationMapping($columnName);
                    $joinColumn = $meta['joinColumns'][0]['name'];
                    $referencedColumnName = $meta['joinColumns'][0]['referencedColumnName'];
                    $targetEntity = $meta['targetEntity'];
                    $qb->leftJoin($targetEntity, $subAlias, Join::WITH, sprintf('%s.%s = %s.%s', $subAlias, $referencedColumnName, $alias, $joinProp));

                    if ($searchableAnnotation->int) {
                        if (is_numeric($this->getQueryParam(self::QUERY_LIKE_SEARCH))) {
                            $searchExpressions[] = sprintf('%s.%s = %s', $subAlias, $searchProp, $this->getQueryParam(self::QUERY_LIKE_SEARCH));
                        }
                    } else {
                        $searchExpressions[] = sprintf('lower(%s.%s) LIKE lower(\'%s\')', $subAlias, $searchProp, $likeSearch);
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

            die(); // REMEMBER TO REMOVE THIS

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

    //Generate excel sheet function

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

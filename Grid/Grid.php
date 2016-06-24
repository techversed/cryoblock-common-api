<?php

namespace Carbon\ApiBundle\Grid;

use Carbon\ApiBundle\Service\CarbonAnnotationReader;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\RequestStack;

abstract class Grid implements GridInterface
{
    /**
     * Query param to send in the request to override the
     * grids per page
     *
     * @var string
     */
    const QUERY_PER_PAGE = "cPerPage";

    /**
     * Query param to send in the request to set the page
     *
     * @var string
     */
    const QUERY_PAGE = "cPage";

    /**
     * Query param to send in the request to set the
     * column we should order the result set by
     *
     * @var string
     */
    const QUERY_ORDER_BY = "cOrderBy";

    /**
     * @var int The default per page for the grid
     */
    const QUERY_LIKE_SEARCH = "cSearch";

    /**
     * Query param to send in the request to set the
     * direction we should order by ASC | DESC
     *
     * @var string
     */
    const QUERY_ORDER_BY_DIRECTION = "cOrderByDirection";

    /**
     * Query param specifying whether to disable Gedmo
     * softdeleteable filter allowing soft deleted entities
     * to be returned in the search results. Valid values are
     * 0 for false and 1 for true
     *
     * @var int
     */
    const QUERY_SHOW_DELETED = "cShowDeleted";

    const QUERY_NOT = "cNot";

    // const QUERY_OPERATORS = array(
    //     'EQ' => '=',
    //     'LTE' => '<=',
    //     'GTE' => '<='
    // );

    /**
     * @var int The default per page for the grid
     */
    const GRID_PER_PAGE = 25;

    /**
     * Array of valid query params
     *
     * @var array
     */
    protected $validGridQueryParams = array(
        self::QUERY_PER_PAGE,
        self::QUERY_PAGE,
        self::QUERY_ORDER_BY,
        self::QUERY_LIKE_SEARCH,
        self::QUERY_ORDER_BY_DIRECTION,
        self::QUERY_SHOW_DELETED,
        self::QUERY_NOT,
    );

    /**
     * @var Symfony\Component\HttpFoundation\RequestStack
     */
    protected $requestStack;

    /**
     * @var Doctrine\Common\Annotations\AnnotationReader
     */
    protected $annotationReader;

    /**
     * @var Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @var int How many results to return
     */
    protected $perPage;

    /**
     * @var int the current page
     */
    protected $page;

    /**
     * @var int unpaginated total
     */
    protected $unpaginatedTotal;

    /**
     * @var int paginated total
     */
    protected $paginatedTotal;

    /**
     * Initialize new CarbonGrid instance
     *
     * @param RequestStack $requestStack
     */
    public function __construct(
        RequestStack $requestStack,
        CarbonAnnotationReader $annotationReader,
        EntityManager $em

    )
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->annotationReader = $annotationReader;
        $this->em = $em;
    }

    /**
     * Get amount of results per page
     *
     * @return int
     */
    protected function getPerPage()
    {
        // check to see if it was already set
        if ($this->perPage) {
            return $this->perPage;
        }

        return $this->perPage = (int) $this->getQueryParam(self::QUERY_PER_PAGE)
            ?: static::GRID_PER_PAGE
        ;
    }

    /**
     * Get the current page
     *
     * @return int
     */
    protected function getPage()
    {
        if ($this->page) {
            return $this->page;
        }

        return $this->getQueryParam(self::QUERY_PAGE) ?: 1;
    }

    /**
     * Get how many results we must offset by
     *
     * @return int
     */
    protected function getOffset()
    {
        return ($this->getPage() - 1) * $this->getPerPage();
    }

    /**
     * Get the column we should order by
     *
     * @return array | null
     */
    protected function getOrderBy()
    {
        if ($orderBy = $this->getQueryParam(self::QUERY_ORDER_BY)) {
            return array(
                $orderBy,
                $this->getQueryParam(self::QUERY_ORDER_BY_DIRECTION),
            );
        }

        return null;
    }

    /**
     * Get the like search text
     *
     * @return string | null
     */
    protected function getLikeSearchString()
    {
        $likeSearchText = $this->getQueryParam(self::QUERY_LIKE_SEARCH);

        if ($likeSearchText !== NULL) {
            return "%".str_replace(' ', '%', $likeSearchText)."%";
        }

        return null;
    }

    /**
     * Is cShowDeleted param set to true
     *
     * @return boolean
     */
    protected function shouldShowDeleted()
    {
        return TRUE === (bool) $this->getQueryParam(self::QUERY_SHOW_DELETED);
    }

    /**
     * Get a query param from the request
     *
     * @param  string $param
     * @return mixed
     */
    protected function getQueryParam($param)
    {
        return $this->request->query->get($param);
    }

    /**
     * Set the unpaginated total
     *
     * @param int
     */
    protected function setUnpaginatedTotal($unpaginatedTotal)
    {
        $this->unpaginatedTotal = $unpaginatedTotal;
    }

    /**
     * Set the paginated total
     *
     * @param int
     */
    protected function setPaginatedTotal($paginatedTotal)
    {
        $this->paginatedTotal = $paginatedTotal;
    }

    /**
     * Determine if there is a next page
     *
     * @return boolean
     */
    protected function hasNextPage()
    {
        return (($this->getPage() - 1) * $this->getPerPage() + $this->paginatedTotal) != $this->unpaginatedTotal;
    }

    /**
     * Builds the grid response result
     *
     * @param  array $data response data
     * @return array
     */
    protected function buildGridResponse(array $data)
    {
        $this->setPaginatedTotal(count($data));

        return array(
            'page' => $this->getPage(),
            'perPage' => $this->getPerPage(),
            'hasNextPage' => $this->hasNextPage(),
            'unpaginatedTotal' => $this->unpaginatedTotal,
            'paginatedTotal' => $this->paginatedTotal,
            'data' => $data
        );
    }

    public function getFilteredValueMap()
    {
        return $this->getQueryParam(self::QUERY_NOT);
    }
}

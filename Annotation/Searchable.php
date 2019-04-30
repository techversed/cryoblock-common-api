<?php

namespace Carbon\ApiBundle\Annotation;

/**
    The searchable annoatation is used in the file Carbon\ApiBundle\Grid\CarbonGrid.php to handle searching -- still needs modifications in order to efficiently handle MTM relations.
*/


/**
 * @Annotation
 * @Target({"PROPERTY", "CLASS"})
 */
final class Searchable
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var boolean
     */
    public $int;

    /**
     * @var boolean
     */
    public $join;

    /**
     * @var string
     */
    public $joinProp;

    /**
     * @var string
     */
    public $searchProp;

    /**
     * @var string
     */
    public $subAlias;

}

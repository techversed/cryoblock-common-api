<?php

namespace Carbon\ApiBundle\Annotation;

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

    // Boolean that is used when the object that you are searching is linked in an intermediate table
    /**
     * @var boolean
     */
    public $linkerSearch;

    // There will need to be an earlier query which finds the ids that can be in the mtm for a query of this type.
    /**
     * @var string
     */
    public $linkerRepo;

    // This is the entity that will actually be searched.
    /**
     * @var string
     */
    public $linkedObjectRepo;

    //
    //public $mtmFields;
}

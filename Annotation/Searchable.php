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
    public $subAlias;
}

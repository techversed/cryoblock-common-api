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
}

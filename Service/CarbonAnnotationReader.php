<?php

namespace Carbon\ApiBundle\Service;

use Carbon\ApiBundle\Annotation\Searchable;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\Mapping\Column;

/**
 * The carbon annotation reader stores helper methods
 * for carbon services and controllers to use for reading
 * entity annotations.
 *
 * @author Andre Jon Branchizio <andrejbranch@gmail.com>
 * @version 1.01
 */
class CarbonAnnotationReader
{
    /**
     * Get column names for an entity.
     *
     * @return array
     */
    public function getEntityColumnNames($entityClass)
    {
        $columns = array();
        $reflClass = $this->getEntityReflectionClass($entityClass);
        $reader = $this->getReader();
        foreach ($reflClass->getProperties() as $property) {
            $annotations = $reader->getPropertyAnnotations($property);

            // we should skip this column if its timestampable as Gedmo will handle its change
            if ($reader->getPropertyAnnotation($property, 'Gedmo\Mapping\Annotation\Timestampable')) {
                continue;
            }

            foreach ($annotations as $annotation) {
                if ($annotation instanceof Column) {
                    $columns[] = $annotation->name ?: $property->name;
                }
            }
        }

        return $columns;
    }

    /**
     * Get searchable columns for an entity.
     *
     * @param  string $entityClassName
     * @return array
     */
    public function getSearchableColumns($entityClassName)
    {
        $searchableColumns = array();
        $reflClass = $this->getEntityReflectionClass($entityClassName);

        $reader = $this->getReader();
        foreach ($reflClass->getProperties() as $property) {
            $annotations = $reader->getPropertyAnnotations($property);
            foreach ($annotations as $annotation) {
                if ($annotation instanceof Searchable) {
                    $searchableColumns[] = $annotation->name;
                }
            }
        }

        return $searchableColumns;
    }

    /**
     * Get annotation reader
     *
     * @return Doctrine\Common\Annotations\AnnotationReader
     */
    protected function getReader()
    {
        return new AnnotationReader();
    }

    /**
     * Get reflection class for the entity
     *
     * @param  string $className the entities namespace
     * @return \ReflectionClass
     */
    protected function getEntityReflectionClass($className)
    {
        return new \ReflectionClass($className);
    }
}

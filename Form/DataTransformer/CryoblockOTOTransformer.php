<?php

namespace Carbon\ApiBundle\Form\DataTransformer;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class CryoblockOTOTransformer implements DataTransformerInterface
{
    private $manager;

    public function __construct(EntityManager $em, $entityClass)
    {
        $this->em = $em;
        $this->entityClass = $entityClass;
    }

    /**
     * Do nothing on transform
     *
     * @param  SampleType|null $sampleType
     *
     * @return SampleType
     */
    public function transform($obj)
    {
        return $obj;
    }

    /**
     * Transforms an array to an object (SampleType).
     *
     * @param  array $sampleTypeArray
     *
     * @return Issue|null
     *
     * @throws TransformationFailedException if object (SampleType) is not found.
     */
    public function reverseTransform($objArray)
    {
        if (!$objArray) {
            return;
        }

        $obj = $this->em->getRepository($this->entityClass)
            ->find($objArray['id'])
        ;

        if (NULL === $obj) {
            throw new TransformationFailedException(sprintf(
                'Object with id %s does not exist',
                $objArray['id']
            ));
        }

        return $obj->getId();
    }
}

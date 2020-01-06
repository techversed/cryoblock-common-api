<?php

namespace Carbon\ApiBundle\Form\DataTransformer;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class CryoblockGridformTransformer implements DataTransformerInterface
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

// This is going to be


    // Object is going to be an array that specifies the needed services and contains all of the
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

        // Grab the things that is used to determine how we are going to parse everything

        // $importer = $this->container->get('sample.importer'); // This is going to change to grab a genetic importer at some point
        // This is going to need to

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

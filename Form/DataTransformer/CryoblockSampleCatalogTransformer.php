<?php

namespace Carbon\ApiBundle\Form\DataTransformer;

use AppBundle\Entity\Storage\Catalog;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class CryoblockSampleCatalogTransformer implements DataTransformerInterface
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
        if (is_array($objArray)) {

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

        } else {

            $objExists = $this->em->getRepository($this->entityClass)
                ->findOneByName($objArray)
            ;

            // catalog already exists
            if ($objExists) {
                return $objExists->getId();
            }

            $catalog = new Catalog();
            $catalog->setName($objArray);
            $catalog->setStatus('Available');
            $this->em->persist($catalog);
            $this->em->flush();

            return $catalog->getId();

        }

    }
}

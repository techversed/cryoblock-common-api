<?php

namespace Carbon\ApiBundle\Form\DataTransformer;

use AppBundle\Entity\Storage\Catalog;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;


// This is not being handled in the ideal way -- We are going to have to handle target name decisions both here and in the gridform parsing code if we do things this way.

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

            // Not certain that we want to handle this here
            // SEARCH FOR NEW AT FRONT OF EXPRESSION -- if it is there then we handle it.

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

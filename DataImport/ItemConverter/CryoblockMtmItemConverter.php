<?php

namespace Carbon\ApiBundle\DataImport\ItemConverter;

use AppBundle\Entity\Storage\SampleTag;
use Ddeboer\DataImport\ItemConverter\ItemConverterInterface;
use Doctrine\ORM\EntityManager;
use Symfony\Component\PropertyAccess\PropertyAccess;

class CryoblockMtmItemConverter implements ItemConverterInterface
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var string
     */
    protected $property;

    /**
     * @var string
     */
    protected $mtmClass;

    public function __construct(EntityManager $em, $inputClass, $accessor, $childAccessor, $formProperty)
    {
        $this->em = $em;
        $this->inputClass = $inputClass; // AppBundle\\Entity\\Storage\\Sample;
        $this->accessor = $accessor; // sampleTags;
        $this->childAccessor = $childAccessor; // tag;
        $this->formProperty = $formProperty; // tags;
    }

    /**
     * {@inheritDoc}
     */
    public function convert($input)
    {
        $parentMappings = $this->em->getMetadataFactory()->getMetadataFor($this->inputClass);
        $parentAssociationMappings = $parentMappings->getAssociationMapping($this->accessor);

        $mtmEntity = $parentAssociationMappings['targetEntity'];

        $mtmMappings = $this->em->getMetadataFactory()->getMetadataFor($mtmEntity);
        $mtmAssociationMappings = $mtmMappings->getAssociationMapping($this->childAccessor);
        $childEntity = $mtmAssociationMappings['targetEntity'];

        $propertyAccessor = PropertyAccess::createPropertyAccessor();

        if (!$input) {
            return;
        }

        $currentItemIds = array();
        $addingItemIds = array();
        $removingItemIds = array();

        if (array_key_exists('id', $input)) {

            $inputObject = $this->em->getRepository($this->inputClass)->find($input['id']);
            $currentItems = $propertyAccessor->getValue($inputObject, $this->accessor);
            foreach ($currentItems as $currentItem) {
                $currentItemIds[] = $propertyAccessor->getValue($currentItem, $this->childAccessor . 'Id');
            }

        }

        $finalItemIds = $input[$this->accessor] ? explode(',', $input[$this->accessor]) : array();

        foreach ($currentItemIds as $currentItemId) {
            if (!in_array($currentItemId, $finalItemIds)) {
                $removingItemIds[] = $currentItemId;
            }
        }

        foreach ($finalItemIds as $finalItemId) {
            if (!in_array($finalItemId, $currentItemIds)) {
                $addingItemIds[] = $finalItemId;
            }
        }

        $input[$this->formProperty] = array(
            'adding' => $addingItemIds,
            'removing' => $removingItemIds,
        );

        if (array_key_exists('id', $input)) {
            $input[$this->formProperty]['parentId'] = $input['id'];
        }

        $input[$this->accessor] = array();
        foreach ($finalItemIds as $finalItemId) {
            $className = $mtmEntity;
            $mtmObject = new $className();
            $propertyAccessor->setValue($mtmObject, $this->childAccessor, $this->em->getRepository($childEntity)->find($finalItemId));
            $input[$this->accessor][] = $mtmObject;
        }
        return $input;
    }
}

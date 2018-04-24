<?php

namespace Carbon\ApiBundle\DataImport\ValueConverter;

use AppBundle\Entity\Storage\SampleTag;
use Doctrine\Common\Persistence\ObjectRepository;
use Ddeboer\DataImport\ValueConverter\ValueConverterInterface;

/**
 * Converts a string to an object
 *
 * @author Markus Bachmann <markus.bachmann@bachi.biz>
 */
class CryoblockMtmValueConverter implements ValueConverterInterface
{
    /**
     * @var ObjectRepository
     */
    protected $repository;

    /**
     * @var string
     */
    protected $property;

    /**
     * @var string
     */
    protected $mtmClass;

    public function __construct(ObjectRepository $repository, $property, $mtmClass)
    {
        $this->repository = $repository;
        $this->property = $property;
        $this->mtmClass = $mtmClass;
    }

    /**
     * {@inheritDoc}
     */
    public function convert($input)
    {
        if (!$input) {
            return;
        }
        $tagIds = explode(',', $input);
        $tags = array();

        foreach ($tagIds as $tagId) {
            $sampleTag = new $this->mtmClass();
            $sampleTag->setTag($this->repository->find($tagId));
            $tags[] = $sampleTag;
        }

        return $tags;
    }
}

<?php

namespace Carbon\ApiBundle\Entity\Storage\Sequence;

use AppBundle\Entity\Storage\SampleType;
use AppBundle\Entity\Storage\StorageContainer;
use Carbon\ApiBundle\Annotation AS Carbon;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation AS JMS;
use Symfony\Component\Validator\Constraints as Assert;

/*
    Written by Taylor Jones
        This class was created to be implemented by any class which links a sequence to a sample
        Later on there will likely be several different types of sequences
            --Antibody sequences
            --Genomic sequences
            --Synthetic sequences (vector backbones and other similar sequences which are used in the gene synthesis process)

*/

/** @ORM\MappedSuperclass */
abstract class BaseSequenceCatalog
{
    abstract public function getSequence();
    abstract public function setSequence($seq);

    abstract public function getCatalog();
    abstract public function setCatalog($cat);

    abstract public function getCatalogId();
    abstract public function setCatalogId($catId);

    abstract public function getSequenceId();
    abstract public function setSequenceId($seqId);
}

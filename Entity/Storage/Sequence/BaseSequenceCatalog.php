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


*/

/** @ORM\MappedSuperclass */
abstract class BaseSequenceCatalog
{

    // The stuff that we had here linked specifically to antibody sequence outside of common... I decided to move all of this to the crowelab branch instead of having it in BaseSampleSequence
    // It does not make sense for use to even have a class in base

    // Datastructure that is used to convert amino acids to a nucleotide sequence that could potentially code for them.

    abstract public function getSequence();
    abstract public function setSequence($seq);

    abstract public function getCatalog();
    abstract public function setCatalog($cat);

    abstract public function getCatalogId();
    abstract public function setCatalogId($catId);

    abstract public function getSequenceId();
    abstract public function setSequenceId($seqId);

}

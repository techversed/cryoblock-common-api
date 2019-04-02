<?php

namespace Carbon\ApiBundle\Entity\Storage;

use AppBundle\Entity\Storage\SampleType;
use AppBundle\Entity\Storage\StorageContainer;
use Carbon\ApiBundle\Annotation AS Carbon;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation AS JMS;
use Symfony\Component\Validator\Constraints as Assert;


/** @ORM\MappedSuperclass */
abstract class BaseSampleSequence
{

    // The stuff that we had here linked specifically to antibody sequence outside of common... I decided to move all of this to the crowelab branch instead of having it in BaseSampleSequence
    // It does not make sense for use to even have a class in base

    // Datastructure that is used to convert amino acids to a nucleotide sequence that could potentially code for them.


}

<?php
/*
CHANGES THAT I THINK WE SHOULD MAKE
    Need to add projects to the form type
    Need to add projects to the sequence importer
    Need to add support for generating the amino sequence if the nucleotide sequence is given



*/

namespace Carbon\ApiBundle\Entity\Storage;

use Carbon\ApiBundle\Annotation AS Carbon;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation AS JMS;
use Symfony\Component\Validator\Constraints as Assert;
use Carbon\ApiBundle\Entity\BaseCryoblockEntity;

/** @ORM\MappedSuperclass */
abstract class BaseSequence extends BaseCryoblockEntity
{

    // I decided to move a lot of this into the crowelab repo instead of the cryoblock common repo -- it just was not working out.
    // Base sequence should be set up to handle any type of sequence and we are really only going to support
}

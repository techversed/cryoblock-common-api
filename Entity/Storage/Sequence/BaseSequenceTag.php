<?php

namespace Carbon\ApiBundle\Entity\Storage\Sequence;

use JMS\Serializer\Annotation AS JMS;
use Carbon\ApiBundle\Annotation AS Carbon;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/** @ORM\MappedSuperclass */
abstact class BaseSequenceTag
{

    abstract public function getTagId();
    abstract public function setTagId($tagId);

    abstract public function getTag();
    abstract public function setTag($tag);

    abstract public function getSequence();
    abstract public function setSequence($sequence);

    abstract public function getSequenceId();
    abstract public function setSequence($sequenceId);

}

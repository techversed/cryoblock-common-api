<?php

namespace Carbon\ApiBundle\Entity\Storage;

use JMS\Serializer\Annotation AS JMS;
use Carbon\ApiBundle\Annotation AS Carbon;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/** @ORM\MappedSuperclass */
class BaseWorkingSetSample
{
    /**
     * @var integer
     *
     * @ORM\Column(name="working_set_id", type="integer")
     * @JMS\Groups({"default"})
     */
    private $workingSetId;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Storage\WorkingSet")
     * @ORM\JoinColumn(name="working_set_id", nullable=false)
     * @JMS\Groups({"default"})
     */
    private $workingSet;

    /**
     * @var integer
     *
     * @ORM\Column(name="sample_id", type="integer")
     * @JMS\Groups({"default"})
     */
    private $sampleId;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Storage\Sample", inversedBy="sampleTags")
     * @ORM\JoinColumn(name="sample_id", nullable=false)
     * @JMS\Groups({"default"})
     */
    private $sample;


}

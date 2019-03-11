<?php

namespace Carbon\ApiBundle\Entity\Storage;

use Carbon\ApiBundle\Annotation AS Carbon;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation AS JMS;
use Symfony\Component\Validator\Constraints as Assert;
use Carbon\ApiBundle\Entity\Storage\BaseDivisionAccessGovernor;

/** @ORM\MappedSuperclass */
abstract class BaseDivisionSampleType extends BaseDivisionAccessGovernor
{

// Constants

// Implemenations of Abstract Classes
    public function getAccessGovernor ()
    {
        return $this->getSampleType();
    }
    public function setAccessGovernor($ag) {
        return $this->setSampleType($ag);
    }
    public function getAccessGovernorId() {
        return $this->getSampleTypeId();
    }
    public function setAccessGovernorId($id) {
        return $this->setSampleType($id);
    }
    public function testing()
    {
        return $this->getDivision()->getId();
    }

// Attributes

    /**
     * @var integer
     *
     * @ORM\Column(name="sample_type_id", type="integer")
     * @JMS\Groups({"default"})
     */
    protected $sampleTypeId;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Storage\SampleType")
     * @ORM\JoinColumn(name="sample_type_id", nullable=false)
     * @JMS\Groups({"default"})
     */
    protected $sampleType;


// Getters and setters
    /**
     * Gets the value of sampleTypeId.
     *
     * @return integer
     */
    public function getSampleTypeId()
    {
        return $this->sampleTypeId;
    }

    /**
     * Sets the value of sampleTypeId.
     *
     * @param integer $sampleTypeId the sample type id
     *
     * @return self
     */
    public function setSampleTypeId($sampleTypeId)
    {
        $this->sampleTypeId = $sampleTypeId;

        return $this;
    }

    /**
     * Gets the value of sampleType.
     *
     * @return mixed
     */
    public function getSampleType()
    {
        return $this->sampleType;
    }

    /**
     * Sets the value of sampleType.
     *
     * @param mixed $sampleType the sample type
     *
     * @return self
     */
    public function setSampleType($sampleType)
    {
        $this->sampleType = $sampleType;

        return $this;
    }

}

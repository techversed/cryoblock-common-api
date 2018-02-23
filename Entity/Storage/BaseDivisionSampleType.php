<?php

namespace Carbon\ApiBundle\Entity\Storage;

use JMS\Serializer\Annotation AS JMS;
use Carbon\ApiBundle\Annotation AS Carbon;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/** @ORM\MappedSuperclass */
class BaseDivisionSampleType
{
    /**
     * @var integer
     *
     * @ORM\Column(name="sample_type_id", type="integer")
     * @JMS\Groups({"default"})
     */
    private $sampleTypeId;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Storage\SampleType")
     * @ORM\JoinColumn(name="sample_type_id", nullable=false)
     * @JMS\Groups({"default"})
     */
    private $sampleType;

    /**
     * @var integer
     *
     * @ORM\Column(name="division_id", type="integer")
     * @JMS\Groups({"default"})
     */
    private $divisionId;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Storage\Division", inversedBy="divisionSampleTypes")
     * @ORM\JoinColumn(name="division_id", nullable=false)
     * @JMS\Groups({"default"})
     */
    private $division;

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

    /**
     * Gets the value of divisionId.
     *
     * @return integer
     */
    public function getDivisionId()
    {
        return $this->divisionId;
    }

    /**
     * Sets the value of divisionId.
     *
     * @param integer $divisionId the division id
     *
     * @return self
     */
    public function setDivisionId($divisionId)
    {
        $this->divisionId = $divisionId;

        return $this;
    }

    /**
     * Gets the value of division.
     *
     * @return mixed
     */
    public function getDivision()
    {
        return $this->division;
    }

    /**
     * Sets the value of division.
     *
     * @param mixed $division the division
     *
     * @return self
     */
    public function setDivision($division)
    {
        $this->division = $division;

        return $this;
    }
}

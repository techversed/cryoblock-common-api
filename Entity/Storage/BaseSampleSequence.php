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
class BaseSampleSequence
{

    /**
     * Sample id
     * @ORM\Column(name="sample_id", type="integer", nullable=false)
     * @JMS\Groups({"default"})
     */
    protected $sampleId;

    /**
     * @var Sample $sample
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Storage\Sample")
     * @ORM\JoinColumn(name="sample_id", referencedColumnName="id")
     * @Gedmo\Versioned
     * @JMS\Groups({"default"})
     */
    protected $sample;

    /**
     * Sequence id
     * @ORM\Column(name="sequence_id", type="integer", nullable=false)
     * @JMS\Groups({"default"})
     */
    protected $sequenceId;

    /**
     * @var Sequence $sequence
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Storage\Sequence")
     * @ORM\JoinColumn(name="sequence_id", referencedColumnName="id")
     * @Gedmo\Versioned
     * @JMS\Groups({"default"})
     */
    protected $sequence;

    /**
     * @var string
     *
     * @ORM\Column(name="relationship_type", type="text")
     * @Gedmo\Versioned
     * @JMS\Groups({"default"})
     */
    protected $relationshipType; // Discovery or Insert or Construct

    /**
     * @var string
     *
     * @ORM\Column(name="lot", type="text")
     * @Gedmo\Versioned
     * @JMS\Groups({"default"})
     */
    protected $lot; // What sequencing request created this?

    /**
     * @var \DateTime $deletedAt
     *
     * @ORM\Column(name="deleted_at", type="datetime", nullable=true)
     * @JMS\Groups({"default"})
     */
    protected $deletedAt;

    /**
     * Gets the Sample id.
     *
     * @return mixed
     */
    public function getSampleId()
    {
        return $this->sampleId;
    }

    /**
     * Sets the Sample id.
     *
     * @param mixed $sampleId the sample id
     *
     * @return self
     */
    public function setSampleId($sampleId)
    {
        $this->sampleId = $sampleId;

        return $this;
    }

    /**
     * Gets the value of sample.
     *
     * @return Sample $sample
     */
    public function getSample()
    {
        return $this->sample;
    }

    /**
     * Sets the value of sample.
     *
     * @param Sample $sample $sample the sample
     *
     * @return self
     */
    public function setSample($sample)
    {
        $this->sample = $sample;

        return $this;
    }

    /**
     * Gets the Sequence id.
     *
     * @return mixed
     */
    public function getSequenceId()
    {
        return $this->sequenceId;
    }

    /**
     * Sets the Sequence id.
     *
     * @param mixed $sequenceId the sequence id
     *
     * @return self
     */
    public function setSequenceId($sequenceId)
    {
        $this->sequenceId = $sequenceId;

        return $this;
    }

    /**
     * Gets the value of sequence.
     *
     * @return Sequence $sequence
     */
    public function getSequence()
    {
        return $this->sequence;
    }

    /**
     * Sets the value of sequence.
     *
     * @param Sequence $sequence $sequence the sequence
     *
     * @return self
     */
    public function setSequence($sequence)
    {
        $this->sequence = $sequence;

        return $this;
    }

    /**
     * Gets the value of relationshipType.
     *
     * @return string
     */
    public function getRelationshipType()
    {
        return $this->relationshipType;
    }

    /**
     * Sets the value of relationshipType.
     *
     * @param string $relationshipType the relationship type
     *
     * @return self
     */
    public function setRelationshipType($relationshipType)
    {
        $this->relationshipType = $relationshipType;

        return $this;
    }

    /**
     * Gets the value of lot.
     *
     * @return string
     */
    public function getLot()
    {
        return $this->lot;
    }

    /**
     * Sets the value of lot.
     *
     * @param string $lot the lot
     *
     * @return self
     */
    public function setLot($lot)
    {
        $this->lot = $lot;

        return $this;
    }

    /**
     * Gets the value of deletedAt.
     *
     * @return \DateTime $deletedAt
     */
    public function getDeletedAt()
    {
        return $this->deletedAt;
    }

    /**
     * Sets the value of deletedAt.
     *
     * @param \DateTime $deletedAt $deletedAt the deleted at
     *
     * @return self
     */
    public function setDeletedAt($deletedAt)
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }
}

<?php

namespace Carbon\ApiBundle\Entity\Storage;

use JMS\Serializer\Annotation AS JMS;
use Carbon\ApiBundle\Annotation AS Carbon;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/** @ORM\MappedSuperclass */
abstract class BaseSampleTag
{
    /**
     * @var integer
     *
     * @ORM\Column(name="tag_id", type="integer")
     * @JMS\Groups({"default"})
     */
    private $tagId;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Storage\Tag")
     * @ORM\JoinColumn(name="tag_id", nullable=false)
     * @JMS\Groups({"default"})
     */
    private $tag;

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

    public function __clone()
    {
        $this->id = null;
    }

    /**
     * Gets the value of tagId.
     *
     * @return integer
     */
    public function getTagId()
    {
        return $this->tagId;
    }

    /**
     * Sets the value of tagId.
     *
     * @param integer $tagId the tag id
     *
     * @return self
     */
    public function setTagId($tagId)
    {
        $this->tagId = $tagId;

        return $this;
    }

    /**
     * Gets the value of tag.
     *
     * @return mixed
     */
    public function getTag()
    {
        return $this->tag;
    }

    /**
     * Sets the value of tag.
     *
     * @param mixed $tag the tag
     *
     * @return self
     */
    public function setTag($tag)
    {
        $this->tag = $tag;

        return $this;
    }

    /**
     * Gets the value of sampleId.
     *
     * @return integer
     */
    public function getSampleId()
    {
        return $this->sampleId;
    }

    /**
     * Sets the value of sampleId.
     *
     * @param integer $sampleId the sample id
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
     * @return mixed
     */
    public function getSample()
    {
        return $this->sample;
    }

    /**
     * Sets the value of sample.
     *
     * @param mixed $sample the sample
     *
     * @return self
     */
    public function setSample($sample)
    {
        $this->sample = $sample;

        return $this;
    }
}

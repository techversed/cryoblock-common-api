<?php

namespace Carbon\ApiBundle\Entity\Storage;

use JMS\Serializer\Annotation AS JMS;
use Carbon\ApiBundle\Annotation AS Carbon;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/** @ORM\MappedSuperclass */
class BaseSequenceTag
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
     * @ORM\Column(name="sequence_id", type="integer")
     * @JMS\Groups({"default"})
     */
    private $sequenceId;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Storage\Sequence", inversedBy="sampleTags")
     * @ORM\JoinColumn(name="sample_id", nullable=false)
     * @JMS\Groups({"default"})
     */
    private $sequence;

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
     * Gets the value of sequenceId.
     *
     * @return integer
     */
    public function getSequenceId()
    {
        return $this->sequenceId;
    }

    /**
     * Sets the value of sequenceId.
     *
     * @param integer $sequenceId the sequence id
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
     * @return mixed
     */
    public function getSequence()
    {
        return $this->sequence;
    }

    /**
     * Sets the value of sequence.
     *
     * @param mixed $sequence the sequence
     *
     * @return self
     */
    public function setSequence($sequence)
    {
        $this->sequence = $sequence;

        return $this;
    }
}

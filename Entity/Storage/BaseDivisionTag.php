<?php

namespace Carbon\ApiBundle\Entity\Storage;

use JMS\Serializer\Annotation AS JMS;
use Carbon\ApiBundle\Annotation AS Carbon;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/** @ORM\MappedSuperclass */
class BaseDivisionTag
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
     * @ORM\Column(name="division_id", type="integer")
     * @JMS\Groups({"default"})
     */
    private $divisionId;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Storage\Division", inversedBy="divisionTags")
     * @ORM\JoinColumn(name="division_id", nullable=false)
     * @JMS\Groups({"default"})
     */
    private $division;

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
     * @param integer $tagId the sample type id
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
     * @param mixed $tag the sample type
     *
     * @return self
     */
    public function setTag($tag)
    {
        $this->tag = $tag;

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

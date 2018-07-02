<?php

namespace Carbon\ApiBundle\Entity\Storage;

use Carbon\ApiBundle\Annotation AS Carbon;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation AS JMS;
use Symfony\Component\Validator\Constraints as Assert;
use Carbon\ApiBundle\Entity\BaseCryoblockEntity;

/** @ORM\MappedSuperclass */
class BaseSequence extends BaseCryoblockEntity
{

    /**
    * @ORM\Column(name="nucleotide", nullable=false, type="boolean")
    * @JMS\Groups({"default"})
    * @Gedmo\Versioned
    */
    protected $nucleotide; //This will be used when we expand to using multiple -- not implemented yet.

    /**
    * @var integer
    * @ORM\Column(name="donor_id", type="integer", nullable=true)
    */
    protected $donorId;

    /**
    * @ORM\ManyToOne(targetEntity="Appbundle\Entity\Donor\Donor")
    * @ORM\JoinColumns({
    *   @ORM\JoinColumn(name="donor_id", referencedColumnName="id")
    * })
    * @JMS\Groups({"default"})
    * @Gedmo\Versioned
    */
    protected $donor;

    /**
    * @var integer
    * @ORM\Column(name="target_id", type="integer", nullable=true)
    */
    protected $targetId;

    /**
     * @var integer
     *
     * @ORM\Column(name="catalog_id", type="integer")
     * @JMS\Groups({"default"})
     */
    protected $catalogId;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Storage\Catalog")
     * @ORM\JoinColumn(name="catalog_id", nullable=false)
     * @JMS\Groups({"default"})
     */
    protected $catalog;

    /**
    * @ORM\ManyToOne(targetEntity="Appbundle\Entity\Storage\Target")
    * @ORM\JoinColumns({
    *   @ORM\JoinColumn(name="target_id", referencedColumnName="id")
    * })
    * @JMS\Groups({"default"})
    * @Gedmo\Versioned
    */
    protected $target;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Storage\SequenceTag", mappedBy="sequence")
     */
    protected $sequenceTags;

    /**
     * @var integer
     *
     * @ORM\Column(name="parent_id", type="integer", nullable=true)
     * @JMS\Groups({"default"})
     */
    protected $parentId;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Storage\Sample")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
     * })
     * @JMS\Groups({"default"})
     * @Gedmo\Versioned
     */
    protected $parent; // the sequence that this was taken from

    /**
     * @var string
     *
     * @ORM\Column(name="dna_sequence", type="text")
     * @JMS\Groups({"default"})
     * @Gedmo\Versioned
     */
    protected $dnaSequence;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Storage\SequenceViewer", mappedBy="sequence", cascade={"remove"})
     * @JMS\Groups({"default"})
     */
    protected $sequenceViewers; // not sure what I want to do with the serializer groups here.// changed groups from JMS\Groups({"children", "viewers"}) to default

     /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Storage\SequenceViewer", mappedBy="sequence", cascade={"remove"})
     * @JMS\Groups({"default"})
     */
    public $sequenceGroupViewers;  // changed form this to  JMS\Groups({"children", "groupViewers"}) default...

    // transient variables
    public $groupViewers;
    public $viewers;

    /**
     * @JMS\Groups({"default"})
     */
    protected $tags;

    /**
     * @JMS\Groups({"default"})
     */
    protected $errors;


    /**
     * Gets the value of nucleotide.
     *
     * @return mixed
     */
    public function getNucleotide()
    {
        return $this->nucleotide;
    }

    /**
     * Sets the value of nucleotide.
     *
     * @param mixed $nucleotide the nucleotide
     *
     * @return self
     */
    public function setNucleotide($nucleotide)
    {
        $this->nucleotide = $nucleotide;

        return $this;
    }

    /**
     * Gets the value of donorId.
     *
     * @return integer
     */
    public function getDonorId()
    {
        return $this->donorId;
    }

    /**
     * Sets the value of donorId.
     *
     * @param integer $donorId the donor id
     *
     * @return self
     */
    public function setDonorId($donorId)
    {
        $this->donorId = $donorId;

        return $this;
    }

    /**
     * Gets the }).
     *
     * @return mixed
     */
    public function getDonor()
    {
        return $this->donor;
    }

    /**
     * Sets the }).
     *
     * @param mixed $donor the donor
     *
     * @return self
     */
    public function setDonor($donor)
    {
        $this->donor = $donor;

        return $this;
    }

    /**
     * Gets the value of targetId.
     *
     * @return integer
     */
    public function getTargetId()
    {
        return $this->targetId;
    }

    /**
     * Sets the value of targetId.
     *
     * @param integer $targetId the target id
     *
     * @return self
     */
    public function setTargetId($targetId)
    {
        $this->targetId = $targetId;

        return $this;
    }

    /**
     * Gets the value of catalogId.
     *
     * @return integer
     */
    public function getCatalogId()
    {
        return $this->catalogId;
    }

    /**
     * Sets the value of catalogId.
     *
     * @param integer $catalogId the catalog id
     *
     * @return self
     */
    public function setCatalogId($catalogId)
    {
        $this->catalogId = $catalogId;

        return $this;
    }

    /**
     * Gets the value of catalog.
     *
     * @return mixed
     */
    public function getCatalog()
    {
        return $this->catalog;
    }

    /**
     * Sets the value of catalog.
     *
     * @param mixed $catalog the catalog
     *
     * @return self
     */
    public function setCatalog($catalog)
    {
        $this->catalog = $catalog;

        return $this;
    }

    /**
     * Gets the }).
     *
     * @return mixed
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * Sets the }).
     *
     * @param mixed $target the target
     *
     * @return self
     */
    public function setTarget($target)
    {
        $this->target = $target;

        return $this;
    }

    /**
     * Gets the value of sequenceTags.
     *
     * @return mixed
     */
    public function getSequenceTags()
    {
        return $this->sequenceTags;
    }

    /**
     * Sets the value of sequenceTags.
     *
     * @param mixed $sequenceTags the sequence tags
     *
     * @return self
     */
    public function setSequenceTags($sequenceTags)
    {
        $this->sequenceTags = $sequenceTags;

        return $this;
    }

    /**
     * Gets the value of parentId.
     *
     * @return integer
     */
    public function getParentId()
    {
        return $this->parentId;
    }

    /**
     * Sets the value of parentId.
     *
     * @param integer $parentId the parent id
     *
     * @return self
     */
    public function setParentId($parentId)
    {
        $this->parentId = $parentId;

        return $this;
    }

    /**
     * Gets the }).
     *
     * @return mixed
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Sets the }).
     *
     * @param mixed $parent the parent
     *
     * @return self
     */
    public function setParent($parent)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Gets the value of dnaSequence.
     *
     * @return string
     */
    public function getDnaSequence()
    {
        return $this->dnaSequence;
    }

    /**
     * Sets the value of dnaSequence.
     *
     * @param string $dnaSequence the dna sequence
     *
     * @return self
     */
    public function setDnaSequence($dnaSequence)
    {
        $this->dnaSequence = $dnaSequence;

        return $this;
    }

    /**
     * Gets the value of sequenceViewers.
     *
     * @return mixed
     */
    public function getSequenceViewers()
    {
        return $this->sequenceViewers;
    }

    /**
     * Sets the value of sequenceViewers.
     *
     * @param mixed $sequenceViewers the sequence viewers
     *
     * @return self
     */
    public function setSequenceViewers($sequenceViewers)
    {
        $this->sequenceViewers = $sequenceViewers;

        return $this;
    }

    /**
     * Gets the value of sequenceGroupViewers.
     *
     * @return mixed
     */
    public function getSequenceGroupViewers()
    {
        return $this->sequenceGroupViewers;
    }

    /**
     * Sets the value of sequenceGroupViewers.
     *
     * @param mixed $sequenceGroupViewers the sequence group viewers
     *
     * @return self
     */
    public function setSequenceGroupViewers($sequenceGroupViewers)
    {
        $this->sequenceGroupViewers = $sequenceGroupViewers;

        return $this;
    }

    /**
     * Gets the value of groupViewers.
     *
     * @return mixed
     */
    public function getGroupViewers()
    {
        return $this->groupViewers;
    }

    /**
     * Sets the value of groupViewers.
     *
     * @param mixed $groupViewers the group viewers
     *
     * @return self
     */
    public function setGroupViewers($groupViewers)
    {
        $this->groupViewers = $groupViewers;

        return $this;
    }

    /**
     * Gets the value of viewers.
     *
     * @return mixed
     */
    public function getViewers()
    {
        return $this->viewers;
    }

    /**
     * Sets the value of viewers.
     *
     * @param mixed $viewers the viewers
     *
     * @return self
     */
    public function setViewers($viewers)
    {
        $this->viewers = $viewers;

        return $this;
    }

    /**
     * Gets the value of tags.
     *
     * @return mixed
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * Sets the value of tags.
     *
     * @param mixed $tags the tags
     *
     * @return self
     */
    public function setTags($tags)
    {
        $this->tags = $tags;

        return $this;
    }

    /**
     * Gets the value of errors.
     *
     * @return mixed
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Sets the value of errors.
     *
     * @param mixed $errors the errors
     *
     * @return self
     */
    public function setErrors($errors)
    {
        $this->errors = $errors;

        return $this;
    }
}

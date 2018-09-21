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
class BaseSequence extends BaseCryoblockEntity
{
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
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     * @Gedmo\Versioned
     * @JMS\Groups({"default"})
     * @Carbon\Searchable(name="description")
     */
    protected $description;

//....Assert\NotBlank() // stripped from the previous group of entity attributes to get this working

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


    // Boolean to indicate if the nucleotide or the amino acid sequence was specified by the user at time of upload. In the end we might even make it so that we generate the most likely nucleotide sequence when an amino acid sequence is given.
    /**
    * @ORM\Column(name="nucleotide", nullable=false, type="boolean")
    * @JMS\Groups({"default"})
    * @Gedmo\Versioned
    */
    protected $nucleotide;

    // This will not be nullable in the final verison -- The whole point of this object is to store sequence information.

    /**
     * @var string
     *
     * @ORM\Column(name="nucleotide_sequence", type="text", nullable=true)
     * @JMS\Groups({"default"})
     * @Gedmo\Versioned
     * @Carbon\Searchable(name="nucleotide_sequence")
     */
    protected $nucleotideSequence;

    /**
     * @var string
     *
     * @ORM\Column(name="amino_acid_sequence", type="text", nullable=true)
     * @JMS\Groups({"default"})
     * @Carbon\Searchable(name="amino_acid_sequence")
     * @Gedmo\Versioned
     */
    protected $aminoAcidSequence;

    /**
    * @ORM\OneToMany(targetEntity="AppBundle\Entity\Project\ProjectSequence", mappedBy="sequence")
    * @JMS\Groups({"template"})
    */
    protected $projectSequences;


    /*
    transient variables
    */

   /**
     * @JMS\Groups({"default"})
     */
    public $projects;

    /**
     * @JMS\Groups({"default"})
     */
    protected $errors;

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

    /**
     * Gets the value of description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Sets the value of description.
     *
     * @param string $description the description
     *
     * @return self
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

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
        $this->nucleotide = strtoupper($nucleotide);

        return $this;
    }

    /**
     * Gets the value of nucleotideSequence.
     *
     * @return string
     */
    public function getNucleotideSequence()
    {
        return $this->nucleotideSequence;
    }

    /**
     * Sets the value of nucleotideSequence.
     *
     * @param string $nucleotideSequence the nucleotide sequence
     *
     * @return self
     */
    public function setNucleotideSequence($nucleotideSequence)
    {
        $this->nucleotideSequence = strtoupper($nucleotideSequence);

        return $this;
    }

    /**
     * Gets the value of aminoAcidSequence.
     *
     * @return string
     */
    public function getAminoAcidSequence()
    {
        return $this->aminoAcidSequence;
    }

    /**
     * Sets the value of aminoAcidSequence.
     *
     * @param string $aminoAcidSequence the amino acid sequence
     *
     * @return self
     */
    public function setAminoAcidSequence($aminoAcidSequence)
    {
        $this->aminoAcidSequence = $aminoAcidSequence;

        return $this;
    }

    //Need to finish writing this...
    /**
     * @JMS\VirtualProperty()
     * @JMS\Groups({"default"})
     */
    public function getTagString()
    {
        $tagNames = [];

        if ($this->sequenceTags && (is_array($this->sequenceTags) || is_object($this->sequenceTags))) {

            foreach ($this->sequenceTags as $sequenceTag) {

                $tagNames[] = $sequenceTag->getTag()->getName();

            }

            return implode(", ", $tagNames);
        }
    }


    //comment all this out while we get the tags working...
    /**
     * @JMS\VirtualProperty()
     * @JMS\Groups({"default"})
     */
    public function getProjectString()
    {
        $projectNames = [];

        if ($this->projectSequences && (is_array($this->projectSequences) || is_object($this->projectSequences))) {

            foreach ($this->projectSequences as $sequenceProject) {

                $projectNames[] = $sequenceProject->getProject()->getName();

            }

            return implode(", ", $projectNames);
        }
    }

    /**
     * Gets the value of projectSequence.
     *
     * @return mixed
     */
    public function getProjectSequence()
    {
        return $this->projectSequence;
    }

    /**
     * Sets the value of projectSequence.
     *
     * @param mixed $projectSequence the project sequence
     *
     * @return self
     */
    public function setProjectSequence($projectSequence)
    {
        $this->projectSequence = $projectSequence;

        return $this;
    }

    /**
     * Gets the value of projects.
     *
     * @return mixed
     */
    public function getProjects()
    {
        return $this->projects;
    }

    /**
     * Sets the value of projects.
     *
     * @param mixed $projects the projects
     *
     * @return self
     */
    public function setProjects($projects)
    {
        $this->projects = $projects;

        return $this;
    }
}

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
     * @ORM\Column(name="description", type="text")
     * @Gedmo\Versioned
     * @JMS\Groups({"default"})
     * @Carbon\Searchable(name="description")
     * @Assert\NotBlank()
     */
    protected $description;

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
    protected $dnaSequence; //Since we changed this to being base sequence we should probably chagne this away from dna sequence because we would also like to support having other forms of sequences for other labs -- amino acid sequences and rna sequences would be two other types that would be common throughout other labs.

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
}

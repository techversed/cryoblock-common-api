<?php

namespace Carbon\ApiBundle\Entity\Storage;

use AppBundle\Entity\Storage\SampleType;
use AppBundle\Entity\Storage\StorageContainer;
use Carbon\ApiBundle\Annotation AS Carbon;
use Carbon\ApiBundle\Entity\Storage\BaseSample;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation AS JMS;
use Symfony\Component\Validator\Constraints as Assert;

/** @ORM\MappedSuperclass */
class BaseSample
{
    /**
     * Valid concentration units
     *
     * @var array
     */
    protected $validConcentrationUnits = array(
        'mg/mL',
        'ng/uL',
        'Molar',
    );

    /**
     * Valid sample statuses
     *
     * @var array
     */
    protected $validStatuses = array(
        'Available',
        'Depleted',
        'Destroyed',
        'Shipped'
    );

    /**
     * @var Catalog $catalog
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Storage\Catalog")
     * @ORM\JoinColumn(name="catalog_id", referencedColumnName="id")
     * @Gedmo\Versioned
     * @JMS\Groups({"default"})
     */
    protected $catalog;

    /**
     * Catalog id
     * @ORM\Column(name="catalog_id", type="integer", nullable=false)
     * @JMS\Groups({"default"})
     */
    protected $catalogId;

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
     * @var string
     *
     * @ORM\Column(name="notes", type="text", nullable=true)
     * @JMS\Groups({"default"})
     * @Carbon\Searchable(name="notes")
     */
    protected $notes;

    /**
     * @var User $createdBy
     *
     * @Gedmo\Blameable(on="create")
     * @ORM\ManyToOne(targetEntity="Carbon\ApiBundle\Entity\User")
     * @ORM\JoinColumn(name="created_by_id", referencedColumnName="id")
     * @JMS\Groups({"default"})
     */
    protected $createdBy;

    /**
     * Created by id
     * @ORM\Column(name="created_by_id", type="integer", nullable=false)
     * @JMS\Groups({"default"})
     */
    protected $createdById;

    /**
     * @var User $updatedBy
     *
     * @Gedmo\Blameable(on="update")
     * @ORM\ManyToOne(targetEntity="Carbon\ApiBundle\Entity\User")
     * @ORM\JoinColumn(name="updated_by_id", referencedColumnName="id")
     * @JMS\Groups({"default"})
     * @JMS\MaxDepth(1)
     */
    protected $updatedBy;

    /**
     * Created by id
     * @ORM\Column(name="updated_by_id", type="integer", nullable=false)
     * @JMS\Groups({"default"})
     */
    protected $updatedById;

    /**
     * @var integer
     *
     * @ORM\Column(name="division_id", type="integer", nullable=true)
     * @JMS\Groups({"default"})
     */
    protected $divisionId;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Storage\Division", inversedBy="samples")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="division_id", referencedColumnName="id")
     * })
     * @JMS\Groups({"default"})
     * @Gedmo\Versioned
     */
    protected $division;

    /**
     * @var string
     *
     * @ORM\Column(name="division_row", type="string", length=1, nullable=true)
     * @JMS\Groups({"default"})
     * @Gedmo\Versioned
     */
    protected $divisionRow;

    /**
     * @var integer
     *
     * @ORM\Column(name="division_column", type="integer", length=1, nullable=true)
     * @JMS\Groups({"default"})
     * @Gedmo\Versioned
     */
    protected $divisionColumn;

    /**
     * @var \DateTime $created
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     * @JMS\Groups({"default"})
     */
    protected $createdAt;

    /**
     * @var \DateTime $updated
     *
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime")
     * @JMS\Groups({"default"})
     */
    protected $updatedAt;

    /**
     * @var \DateTime $deletedAt
     *
     * @ORM\Column(name="deleted_at", type="datetime", nullable=true)
     * @Gedmo\Versioned
     * @JMS\Groups({"default"})
     */
    protected $deletedAt;

    /**
     * @var SampleType $sampleType
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Storage\SampleType")
     * @ORM\JoinColumn(name="sample_type_id", referencedColumnName="id")
     * @Gedmo\Versioned
     * @JMS\Groups({"default"})
     */
    protected $sampleType;

    /**
     * Created by id
     * @ORM\Column(name="sample_type_id", type="integer", nullable=false)
     * @JMS\Groups({"default"})
     */
    protected $sampleTypeId;

    /**
     * @var StorageContainer $storageContainer
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Storage\StorageContainer")
     * @ORM\JoinColumn(name="storage_container_id", referencedColumnName="id", nullable=false)
     * @Gedmo\Versioned
     * @JMS\Groups({"default"})
     * @Assert\NotNull()
     */
    protected $storageContainer;

    /**
     * Created by id
     * @ORM\Column(name="storage_container_id", type="integer", nullable=false)
     * @JMS\Groups({"default"})
     */
    protected $storageContainerId;

    /**
     * @var string $status
     *
     * @ORM\Column(name="status", type="string", nullable=false)
     * @JMS\Groups({"default"})
     * @Gedmo\Versioned
     */
    protected $status;

    /**
     * @var float $concentration
     *
     * @ORM\Column(name="concentration", type="decimal", precision=20, scale=3, nullable=true)
     * @Gedmo\Versioned
     * @JMS\Groups({"default"})
     * @JMS\Type("double")
     * @Gedmo\Versioned
     */
    protected $concentration;

    /**
     * @var string $concentrationUnits
     *
     * @ORM\Column(name="concentration_units", type="string", nullable=true, length=15)
     * @JMS\Groups({"default"})
     * @Gedmo\Versioned
     */
    protected $concentrationUnits;

    /**
     * @var float $volume
     *
     * @ORM\Column(name="volume", type="decimal", precision=3, nullable=true)
     * @Gedmo\Versioned
     * @JMS\Groups({"default"})
     */
    protected $volume;

    /**
     * @var float $mass
     *
     * @ORM\Column(name="mass", type="decimal", precision=20, scale=3, nullable=true)
     * @Gedmo\Versioned
     * @JMS\Groups({"default"})
     * @JMS\Type("double")
     */
    protected $mass;

    /**
     * @var integer $lot
     *
     * @ORM\Column(name="lot", type="string", length=300, nullable=true)
     * @Gedmo\Versioned
     * @JMS\Groups({"default"})
     * @Carbon\Searchable(name="lot")
     */
    protected $lot;

    /**
     * @JMS\Groups({"default"})
     */
    public $storageRecommended;

    /**
     * @JMS\Groups({"default"})
     */
    public $recommendedDivision;

    /**
     * @JMS\Groups({"default"})
     */
    public $recommendedDivisionRow;

    /**
     * @JMS\Groups({"default"})
     */
    public $recommendedDivisionColumn;

    /**
     * @JMS\Groups({"default"})
     */
    public $errors;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set divisionId
     *
     * @param integer $divisionId
     * @return Sample
     */
    public function setDivisionId($divisionId)
    {
        $this->divisionId = $divisionId;

        return $this;
    }

    /**
     * Get divisionId
     *
     * @return integer
     */
    public function getDivisionId()
    {
        return $this->divisionId;
    }

    /**
     * Set division
     *
     * @param \stdClass $division
     * @return Sample
     */
    public function setDivision($division)
    {
        $this->division = $division;

        return $this;
    }

    /**
     * Get division
     *
     * @return \stdClass
     */
    public function getDivision()
    {
        return $this->division;
    }

    /**
     * Set divisionRow
     *
     * @param string $divisionRow
     * @return Sample
     */
    public function setDivisionRow($divisionRow)
    {
        $this->divisionRow = $divisionRow;

        return $this;
    }

    /**
     * Get divisionRow
     *
     * @return string
     */
    public function getDivisionRow()
    {
        return $this->divisionRow;
    }

    /**
     * Set divisionColumn
     *
     * @param integer $divisionColumn
     * @return Sample
     */
    public function setDivisionColumn($divisionColumn)
    {
        $this->divisionColumn = $divisionColumn;

        return $this;
    }

    /**
     * Get divisionColumn
     *
     * @return integer
     */
    public function getDivisionColumn()
    {
        return $this->divisionColumn;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return Sample
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Get created by id
     *
     * @return integer
     */
    public function getCreatedById()
    {
        return $this->createdById;
    }

    /**
     * Get created by user
     *
     * @return Carbon\ApiBundle\User
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * Get updated by user
     *
     * @return Carbon\ApiBundle\User
     */
    public function getUpdatedBy()
    {
        return $this->updateBy;
    }

    /**
     * Get updated by id
     *
     * @return integer
     */
    public function getUpdatedById()
    {
        return $this->updatedById;
    }

    /**
     * Get notes
     *
     * @return string
     */
    public function getNotes()
    {
        return $this->notes;
    }

    public function getDeletedAt()
    {
        return $this->deletedAt;
    }

    public function setDeletedAt($deletedAt)
    {
        $this->deletedAt = $deletedAt;
    }

    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    public function getSampleType()
    {
        return $this->sampleType;
    }

    public function getSampleTypeId()
    {
        return $this->sampleTypeId;
    }

    public function setSampleTypeId($sampleTypeId)
    {
        $this->sampleTypeId = $sampleTypeId;
    }

    public function setSampleType(SampleType $sampleType = null)
    {
        $this->sampleType = $sampleType;
    }

    public function getStorageContainer()
    {
        return $this->storageContainer;
    }

    public function setStorageContainer(StorageContainer $storageContainer = null)
    {
        $this->storageContainer = $storageContainer;
    }

    public function setStorageContainerId($storageContainerId)
    {
        $this->storageContainerId = $storageContainerId;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @JMS\VirtualProperty()
     * @JMS\Groups({"default"})
     */
    public function getStringLabel()
    {
        if (!is_object($this->getCatalog())) {
            return '';
        }

        return $this->getCatalog()->getName();
    }

    /**
     * Gets the Valid sample statuses.
     *
     * @return array
     */
    public function getValidStatuses()
    {
        return $this->validStatuses;
    }

    /**
     * Sets the Valid sample statuses.
     *
     * @param array $validStatuses the valid statuses
     *
     * @return self
     */
    public function setValidStatuses(array $validStatuses)
    {
        $this->validStatuses = $validStatuses;

        return $this;
    }

    /**
     * Sets the value of id.
     *
     * @param integer $id the id
     *
     * @return self
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Sets the value of notes.
     *
     * @param string $notes the notes
     *
     * @return self
     */
    public function setNotes($notes)
    {
        $this->notes = $notes;

        return $this;
    }

    /**
     * Sets the value of createdBy.
     *
     * @param User $createdBy $createdBy the created by
     *
     * @return self
     */
    public function setCreatedBy($createdBy)
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * Sets the Created by id.
     *
     * @param mixed $createdById the created by id
     *
     * @return self
     */
    public function setCreatedById($createdById)
    {
        $this->createdById = $createdById;

        return $this;
    }

    /**
     * Sets the value of updatedBy.
     *
     * @param User $updatedBy $updatedBy the updated by
     *
     * @return self
     */
    public function setUpdatedBy($updatedBy)
    {
        $this->updatedBy = $updatedBy;

        return $this;
    }

    /**
     * Sets the Created by id.
     *
     * @param mixed $updatedById the updated by id
     *
     * @return self
     */
    public function setUpdatedById($updatedById)
    {
        $this->updatedById = $updatedById;

        return $this;
    }

    /**
     * Sets the value of createdAt.
     *
     * @param \DateTime $created $createdAt the created at
     *
     * @return self
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Sets the value of updatedAt.
     *
     * @param \DateTime $updated $updatedAt the updated at
     *
     * @return self
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Gets the value of storageRecommended.
     *
     * @return mixed
     */
    public function getStorageRecommended()
    {
        return $this->storageRecommended;
    }

    /**
     * Sets the value of storageRecommended.
     *
     * @param mixed $storageRecommended the storage recommended
     *
     * @return self
     */
    public function setStorageRecommended($storageRecommended)
    {
        $this->storageRecommended = $storageRecommended;

        return $this;
    }

    /**
     * Gets the value of recommendedDivision.
     *
     * @return mixed
     */
    public function getRecommendedDivision()
    {
        return $this->recommendedDivision;
    }

    /**
     * Sets the value of recommendedDivision.
     *
     * @param mixed $recommendedDivision the recommended division
     *
     * @return self
     */
    public function setRecommendedDivision($recommendedDivision)
    {
        $this->recommendedDivision = $recommendedDivision;

        return $this;
    }

    /**
     * Gets the value of recommendedDivisionRow.
     *
     * @return mixed
     */
    public function getRecommendedDivisionRow()
    {
        return $this->recommendedDivisionRow;
    }

    /**
     * Sets the value of recommendedDivisionRow.
     *
     * @param mixed $recommendedDivisionRow the recommended division row
     *
     * @return self
     */
    public function setRecommendedDivisionRow($recommendedDivisionRow)
    {
        $this->recommendedDivisionRow = $recommendedDivisionRow;

        return $this;
    }

    /**
     * Gets the value of recommendedDivisionColumn.
     *
     * @return mixed
     */
    public function getRecommendedDivisionColumn()
    {
        return $this->recommendedDivisionColumn;
    }

    /**
     * Sets the value of recommendedDivisionColumn.
     *
     * @param mixed $recommendedDivisionColumn the recommended division column
     *
     * @return self
     */
    public function setRecommendedDivisionColumn($recommendedDivisionColumn)
    {
        $this->recommendedDivisionColumn = $recommendedDivisionColumn;

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
     * Gets the Created by id.
     *
     * @return mixed
     */
    public function getStorageContainerId()
    {
        return $this->storageContainerId;
    }

    public function getConcentration()
    {
        return $this->concentration;
    }

    public function setConcentration($concentration)
    {
        $this->concentration = (string) $concentration;
    }

    public function getConcentrationUnits()
    {
        return $this->concentrationUnits;
    }

    public function setConcentrationUnits($concentrationUnits)
    {
        $this->concentrationUnits = $concentrationUnits;
    }

    /**
     * @JMS\VirtualProperty()
     * @JMS\Groups({"default"})
     */
    public function getConcentrationString()
    {
        return $this->concentration
            ? $this->concentration . ' ' . $this->concentrationUnits
            : ''
        ;
    }

    /**
     * Gets the Valid concentration units.
     *
     * @return array
     */
    public function getValidConcentrationUnits()
    {
        return $this->validConcentrationUnits;
    }

    /**
     * Sets the Valid concentration units.
     *
     * @param array $validConcentrationUnits the valid concentration units
     *
     * @return self
     */
    public function setValidConcentrationUnits(array $validConcentrationUnits)
    {
        $this->validConcentrationUnits = $validConcentrationUnits;

        return $this;
    }

    /**
     * Gets the value of volume.
     *
     * @return float $volume
     */
    public function getVolume()
    {
        return $this->volume;
    }

    /**
     * Sets the value of volume.
     *
     * @param float $volume $volume the volume
     *
     * @return self
     */
    public function setVolume($volume)
    {
        $this->volume = $volume;

        return $this;
    }

    /**
     * Gets the value of mass.
     *
     * @return float $mass
     */
    public function getMass()
    {
        return $this->mass;
    }

    /**
     * Sets the value of mass.
     *
     * @param float $mass $mass the mass
     *
     * @return self
     */
    public function setMass($mass)
    {
        $this->mass = $mass;

        return $this;
    }

    /**
     * Gets the value of lot.
     *
     * @return integer $lot
     */
    public function getLot()
    {
        return $this->lot;
    }

    /**
     * Sets the value of lot.
     *
     * @param integer $lot $lot the lot
     *
     * @return self
     */
    public function setLot($lot)
    {
        $this->lot = $lot;

        return $this;
    }

    /**
     * Gets the value of catalog.
     *
     * @return Catalog $catalog
     */
    public function getCatalog()
    {
        return $this->catalog;
    }

    /**
     * Sets the value of catalog.
     *
     * @param Catalog $catalog $catalog the catalog
     *
     * @return self
     */
    public function setCatalog($catalog)
    {
        $this->catalog = $catalog;

        return $this;
    }

    /**
     * Gets the Catalog id.
     *
     * @return mixed
     */
    public function getCatalogId()
    {
        return $this->catalogId;
    }

    /**
     * Sets the Catalog id.
     *
     * @param mixed $catalogId the catalog id
     *
     * @return self
     */
    public function setCatalogId($catalogId)
    {
        $this->catalogId = $catalogId;

        return $this;
    }
}

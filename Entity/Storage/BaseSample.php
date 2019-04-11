<?php

namespace Carbon\ApiBundle\Entity\Storage;

use AppBundle\Entity\Storage\SampleType;
use AppBundle\Entity\Storage\StorageContainer;
use Carbon\ApiBundle\Annotation AS Carbon;
use Carbon\ApiBundle\Entity\Storage\BaseSample; // This does not need to include itself --- This should be removed
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation AS JMS;
use Symfony\Component\Validator\Constraints as Assert;
use Carbon\ApiBundle\Entity\BaseCryoblockEntity;

/** @ORM\MappedSuperclass */
abstract class BaseSample extends BaseCryoblockEntity
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
        'cells/mL',
        'cells/uL'
    );

    /**
     * Valid volume units
     *
     * @var array
     */
    protected $validVolumeUnits = array(
        'mL',
        'uL',
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
     * @Carbon\Searchable(name="catalog", join=true, searchProp="name", joinProp="catalogId", subAlias="ct")
     * @JMS\Groups({"default"})
     * @JMS\MaxDepth(2)
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
     * @ORM\Column(name="volume", type="decimal", precision=20, scale=3, nullable=true)
     * @Gedmo\Versioned
     * @JMS\Type("double")
     * @JMS\Groups({"default"})
     */
    protected $volume;

    /**
     * @var string $volumeUnits
     *
     * @ORM\Column(name="volume_units", type="string", nullable=true, length=15)
     * @JMS\Groups({"default"})
     * @Gedmo\Versioned
     */
    protected $volumeUnits;

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
    * @ORM\OneToMany(targetEntity="AppBundle\Entity\Project\ProjectSample", mappedBy="sample")
    * @JMS\Groups({"template"})
    */
    protected $projectSamples;

    // The alias of the lot number --
    /**
     * @var string $lot
     *
     * @ORM\Column(name="lot", type="string", length=300, nullable=true)
     * @Gedmo\Versioned
     * @JMS\Groups({"default"})
     * @Carbon\Searchable(name="lot")
     */
    protected $lot;

    // This can be set by a listener -- whenever a new lot is added it should call a listener which sorts things out -- could also be explicitly set on the frontend.
    /**
     * @var int $lotId
     * @ORM\Column(name="lot_id", type="integer", nullable=true)
     *
     * @JMS\Groups({"default"})
     * @Gedmo\Versioned
     */
    protected $lotId;

    /**
     * @var int $lotEntityDetailId
     * @ORM\Column(name="lot_entity_detail_id", type="integer", nullable=true)
     *
     * @JMS\Groups({"default"})
     * @Gedmo\Versioned
     */
    protected $lotEntityDetailId;

    /**
     * @var EntityDetail $lotEntityDetail
     *
     * @ORM\ManyToOne(targetEntity="Carbon\ApiBundle\Entity\EntityDetail")
     * @ORM\JoinColumn(name="lot_entity_detail_id", referencedColumnName="id")
     * @Gedmo\Versioned
     * @JMS\Groups({"default"})
     */
    protected $lotEntityDetail;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Storage\SampleTag", mappedBy="sample")
     * @JMS\Groups({"default"})
     */
    protected $sampleTags;

    /**
     * @JMS\Groups({"default"})
     */
    public $tags;

   /**
     * @JMS\Groups({"default"})
     */
    public $projects;

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

    public function __clone()
    {
        $this->id = null;

        if ($this->sampleTags) {

            $newSampleTags = new ArrayCollection();

            foreach ($this->sampleTags as $sampleTag) {

                $clonedSampleTag = clone $sampleTag;
                $clonedSampleTag->setSample($this);
                $newSampleTags->add($clonedSampleTag);

            }

            $this->sampleTags = $newSampleTags;

        }

        if ($this->projectSamples) {

            $newProjectSamples = new ArrayCollection();

            foreach ($this->projectSamples as $projectSample) {

                $clonedProjectSample = clone $projectSample;
                $clonedProjectSample->setSample($this);
                $newProjectSamples->add($clonedProjectSample);

            }

            $this->projectSamples = $newProjectSamples;

        }

    }

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
     * Get notes
     *
     * @return string
     */
    public function getNotes()
    {
        return $this->notes;
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
        $this->concentration = $concentration == $this->concentration ? $this->concentration : $concentration;
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
        $this->volume = $volume == $this->volume ? $this->volume : $volume;

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

    /**
     * Gets the value of volumeUnits.
     *
     * @return string $volumeUnits
     */
    public function getVolumeUnits()
    {
        return $this->volumeUnits;
    }

    /**
     * Sets the value of volumeUnits.
     *
     * @param string $volumeUnits $volumeUnits the volume units
     *
     * @return self
     */
    public function setVolumeUnits($volumeUnits)
    {
        $this->volumeUnits = $volumeUnits;

        return $this;
    }

    /**
     * @JMS\VirtualProperty()
     * @JMS\Groups({"default"})
     */
    public function getVolumeString()
    {
        return $this->volume
            ? $this->volume . ' ' . $this->volumeUnits
            : ''
        ;
    }

    /**
     * @JMS\VirtualProperty()
     * @JMS\Groups({"default"})
     */
    public function getTagString()
    {
        $tagNames = [];

        if ($this->sampleTags && (is_array($this->sampleTags) || is_object($this->sampleTags))) {

            foreach ($this->sampleTags as $sampleTag) {

                $tagNames[] = $sampleTag->getTag()->getName();

            }

            return implode(", ", $tagNames);
        }
    }

    /**
     * @JMS\VirtualProperty()
     * @JMS\Groups({"default"})
     */
    public function getProjectString()
    {
        $projectNames = [];

        if ($this->projectSamples && (is_array($this->projectSamples) || is_object($this->projectSamples))) {

            foreach ($this->projectSamples as $sampleProject) {

                $projectNames[] = $sampleProject->getProject()->getName();

            }

            return implode(", ", $projectNames);
        }
    }

    /**
     * Gets the value of sampleTags.
     *
     * @return mixed
     */
    public function getSampleTags()
    {
        return $this->sampleTags;
    }

    /**
     * Sets the value of sampleTags.
     *
     * @param mixed $sampleTags the sample tags
     *
     * @return self
     */
    public function setSampleTags($sampleTags)
    {
        $this->sampleTags = $sampleTags;

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
     * Gets the value of projectSamples.
     *
     * @return mixed
     */
    public function getProjectSamples()
    {
        return $this->projectSamples;
    }

    /**
     * Sets the value of projectSamples.
     *
     * @param mixed $projectSamples the project samples
     *
     * @return self
     */
    public function setProjectSamples($projectSamples)
    {
        $this->projectSamples = $projectSamples;

        return $this;
    }

    /**
     * Gets the Valid volume units.
     *
     * @return array
     */
    public function getValidVolumeUnits()
    {
        return $this->validVolumeUnits;
    }

    /**
     * Sets the Valid volume units.
     *
     * @param array $validVolumeUnits the valid volume units
     *
     * @return self
     */
    public function setValidVolumeUnits(array $validVolumeUnits)
    {
        $this->validVolumeUnits = $validVolumeUnits;

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

    /**
     * @return int $lotId
     */
    public function getLotId()
    {
        return $this->lotId;
    }

    /**
     * @param int $lotId $lotId
     *
     * @return self
     */
    public function setLotId($lotId)
    {
        $this->lotId = $lotId;

        return $this;
    }

    /**
     * @return int $lotEntityDetailId
     */
    public function getLotEntityDetailId()
    {
        return $this->lotEntityDetailId;
    }

    /**
     * @param int $lotEntityDetailId $lotEntityDetailId
     *
     * @return self
     */
    public function setLotEntityDetailId($lotEntityDetailId)
    {
        $this->lotEntityDetailId = $lotEntityDetailId;

        return $this;
    }

    /**
     * @return EntityDetail $lotEntityDetail
     */
    public function getLotEntityDetail()
    {
        return $this->lotEntityDetail;
    }

    /**
     * @param EntityDetail $lotEntityDetail $lotEntityDetail
     *
     * @return self
     */
    public function setLotEntityDetail($lotEntityDetail)
    {
        $this->lotEntityDetail = $lotEntityDetail;

        return $this;
    }
}

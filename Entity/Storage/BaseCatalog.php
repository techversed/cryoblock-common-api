<?php

namespace Carbon\ApiBundle\Entity\Storage;

use Carbon\ApiBundle\Annotation AS Carbon;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation AS JMS;
use Symfony\Component\Validator\Constraints as Assert;
use Carbon\ApiBundle\Entity\BaseCryoblockEntity;

/**
 * @ORM\MappedSuperclass
 */
abstract class BaseCatalog extends BaseCryoblockEntity
{
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
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=300)
     * @Gedmo\Versioned
     * @JMS\Groups({"default"})
     * @Carbon\Searchable(name="name")
     * @Assert\NotBlank()
     */
    protected $name;

    /**
     * @var string $status
     *
     * @ORM\Column(name="status", type="string", nullable=false)
     * @JMS\Groups({"default"})
     * @Gedmo\Versioned
     */
    protected $status;

    /**
     * Merged into id
     * @ORM\Column(name="merged_into_id", type="integer", nullable=true)
     * @JMS\Groups({"default"})
    */
    protected $mergedIntoId;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Storage\Catalog")
     * @ORM\JoinColumn(name="merged_into_id", referencedColumnName="id")
     * @JMS\Groups({"default"})
    */
    protected $mergedInto;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Storage\ParentCatalog", mappedBy="parentCatalog")
     * @JMS\Groups({"default"})
     */
    protected $parentCatalogs;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Storage\Sample", mappedBy="catalog")
     * @JMS\Groups({"filledSlots"})
     */
    protected $samples;

    /**
     * Gets the value of name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets the value of name.
     *
     * @param string $name the name
     *
     * @return self
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Gets the value of status.
     *
     * @return string $status
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Sets the value of status.
     *
     * @param string $status $status the status
     *
     * @return self
     */
    public function setStatus($status)
    {
        if (!in_array($status, $this->validStatuses)) {
            throw new \InvalidArgumentException(sprintf('%s is not a valid status', $status));
        }

        $this->status = $status;

        return $this;
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
     * Gets the value of createdAt.
     *
     * @return \DateTime $updated
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Sets the value of createdAt.
     *
     * @param \DateTime $updated $createdAt the created at
     *
     * @return self
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Gets the value of parentCatalogs.
     *
     * @return mixed
     */
    public function getParentCatalogs()
    {
        return $this->parentCatalogs;
    }

    /**
     * Sets the value of parentCatalogs.
     *
     * @param mixed $parentCatalogs the parent catalogs
     *
     * @return self
     */
    public function setParentCatalogs($parentCatalogs)
    {
        $this->parentCatalogs = $parentCatalogs;

        return $this;
    }

    /**
     * @JMS\VirtualProperty()
     * @JMS\Groups({"default"})
     */
    public function getStringLabel()
    {
        return $this->getName();
    }

    /**
     * @return mixed
     */
    public function getMergedIntoId()
    {
        return $this->mergedIntoId;
    }

    /**
     * @param mixed $mergedIntoId
     *
     * @return self
     */
    public function setMergedIntoId($mergedIntoId)
    {
        $this->mergedIntoId = $mergedIntoId;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getMergedInto()
    {
        return $this->mergedInto;
    }

    /**
     * @param mixed $mergedInto
     *
     * @return self
     */
    public function setMergedInto($mergedInto)
    {
        $this->mergedInto = $mergedInto;

        return $this;
    }

    /**
     * @JMS\VirtualProperty()
     * @JMS\Groups({"default"})
     */
    public function getSampleCount()
    {

        $sampleCount = count($this->getSamples());

        return $sampleCount;
    }

    /**
     * Gets the value of samples.
     *
     * @return mixed
     */
    public function getSamples()
    {
        return $this->samples;
    }

    /**
     * Sets the value of samples.
     *
     * @param mixed $samples the samples
     *
     * @return self
     */
    public function setSamples($samples)
    {
        $this->samples = $samples;

        return $this;
    }
}

<?php

namespace Carbon\ApiBundle\Entity\Storage;

use Carbon\ApiBundle\Annotation AS Carbon;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation AS JMS;
use Symfony\Component\Validator\Constraints as Assert;

/** @ORM\MappedSuperclass */
class BaseDivisionStorageContainer
{
    /**
     * @var integer
     *
     * @ORM\Column(name="storage_container_id", type="integer")
     * @JMS\Groups({"default"})
     */
    protected $storageContainerId;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Storage\StorageContainer")
     * @ORM\JoinColumn(name="storage_container_id", nullable=false)
     * @JMS\Groups({"default"})
     */
    protected $storageContainer;

    /**
     * @var integer
     *
     * @ORM\Column(name="division_id", type="integer")
     * @JMS\Groups({"default"})
     */
    protected $divisionId;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Storage\Division", inversedBy="divisionStorageContainers")
     * @ORM\JoinColumn(name="division_id", nullable=false)
     * @JMS\Groups({"default"})
     */
    protected $division;

    /**
     * @var \DateTime $deletedAt
     *
     * @ORM\Column(name="deleted_at", type="datetime", nullable=true)
     * @JMS\Groups({"default"})
     */
    protected $deletedAt;

    /**
     * Gets the value of storageContainerId.
     *
     * @return integer
     */
    public function getStorageContainerId()
    {
        return $this->storageContainerId;
    }

    /**
     * Sets the value of storageContainerId.
     *
     * @param integer $storageContainerId the storage container id
     *
     * @return self
     */
    public function setStorageContainerId($storageContainerId)
    {
        $this->storageContainerId = $storageContainerId;

        return $this;
    }

    /**
     * Gets the value of storageContainer.
     *
     * @return mixed
     */
    public function getStorageContainer()
    {
        return $this->storageContainer;
    }

    /**
     * Sets the value of storageContainer.
     *
     * @param mixed $storageContainer the storage container
     *
     * @return self
     */
    public function setStorageContainer($storageContainer)
    {
        $this->storageContainer = $storageContainer;

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

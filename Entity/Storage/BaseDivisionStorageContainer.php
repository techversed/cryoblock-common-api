<?php

namespace Carbon\ApiBundle\Entity\Storage;

use Carbon\ApiBundle\Annotation AS Carbon;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation AS JMS;
use Symfony\Component\Validator\Constraints as Assert;
use Carbon\ApiBundle\Entity\Storage\BaseDivisionAccessGovernor;

/** @ORM\MappedSuperclass */
class BaseDivisionStorageContainer extends BaseDivisionAccessGovernor
{

// Constants

// Implementaitons of Parent's Abstract classes
    public function getAccessGovernor () {
        $this->getStorageContainer();
    }

    public function setAccessGovernor ($ag) {
        $this->setStorageContainer($ag);
    }

    public function getAccessGovernorId () {
        $this->getStorageContainerId();
    }

    public function setAccessGovernorId ($id) {
        $this->setStorageContainer($id);
    }

//Attributes
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


// Getters and setters
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
}

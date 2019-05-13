<?php

namespace Carbon\ApiBundle\Entity\Storage;

use Carbon\ApiBundle\Annotation AS Carbon;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation AS JMS;
use Symfony\Component\Validator\Constraints as Assert;

/*
    This class serves as a base class for all things which govern access to storage divisions


    static::


    // Future plans -- change get
        // Add another abstract function which makes it so that this same pricipal can be used with virtually any groupi
            //abstract getGroupingElement() -- gets the grouping that is used for grouping documents or simulating a filesystem or whatever the case may be.


*/

/** @ORM\MappedSuperClass */
abstract class BaseDivisionAccessGovernor {

// Abstract functions
    abstract public function getId();
    abstract public function getAccessorColumnName();
    abstract public function setId($id);
    abstract public function getAccessGovernor();
    abstract public function setAccessGovernor($ag);
    abstract public function getAccessGovernorId();
    abstract public function setAccessGovernorId($id);

// Attributes
    /**
     * @var integer
     *
     * @ORM\Column(name="division_id", type="integer")
     * @JMS\Groups({"default"})
     */
    protected $divisionId;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Storage\Division", inversedBy="divisionEditors")
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

// Transient variables

// Getters and setters

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

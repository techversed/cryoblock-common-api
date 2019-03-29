<?php

namespace Carbon\ApiBundle\Entity\Storage;

use Carbon\ApiBundle\Annotation AS Carbon;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation AS JMS;
use Symfony\Component\Validator\Constraints as Assert;
use Carbon\ApiBundle\Entity\Storage\BaseDivisionAccessGovernor;

/** @ORM\MappedSuperclass */
abstract class BaseDivisionGroupViewer extends BaseDivisionAccessGovernor
{

// Constants

// Implementations of parent's abstract classes
    public function getAccessorColumnName()
    {
        return "group_id";
    }

    public function getAccessGovernor ()
    {
        return $this->getGroup();
    }

    public function setAccessGovernor ($ag)
    {
        return $this->setGroup($ag);
    }

    public function getAccessGovernorId ()
    {
        return $this->getGroupId();
    }

    public function setAccessGovernorId ($id)
    {
        return $this->setGroupId($id);
    }

// Attributes
    /**
     * @var integer
     *
     * @ORM\Column(name="group_id", type="integer")
     * @JMS\Groups({"default"})
     */
    protected $groupId;

    /**
     * @ORM\ManyToOne(targetEntity="Carbon\ApiBundle\Entity\Group")
     * @ORM\JoinColumn(name="group_id", nullable=false)
     * @JMS\Groups({"default"})
     */
    protected $group;

// Transient Variables

// Virtual Properties

// Getters and Setters

    /**

     * Gets the value of groupId.
     *
     * @return integer
     */
    public function getGroupId()
    {
        return $this->groupId;
    }

    /**
     * Sets the value of groupId.
     *
     * @param integer $groupId the group id
     *
     * @return self
     */
    public function setGroupId($groupId)
    {
        $this->groupId = $groupId;

        return $this;
    }

    /**
     * Gets the value of group.
     *
     * @return mixed
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * Sets the value of group.
     *
     * @param mixed $group the group
     *
     * @return self
     */
    public function setGroup($group)
    {
        $this->group = $group;

        return $this;
    }
}

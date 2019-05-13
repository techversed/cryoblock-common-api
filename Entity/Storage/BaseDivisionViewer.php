<?php

namespace Carbon\ApiBundle\Entity\Storage;

use JMS\Serializer\Annotation AS JMS;
use Carbon\ApiBundle\Annotation AS Carbon;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use Carbon\ApiBundle\Entity\Storage\BaseDivisionAccessGovernor;

/** @ORM\MappedSuperclass */
abstract class BaseDivisionViewer extends BaseDivisionAccessGovernor
{

// Constants

// Implementations of parents abstract classes
    public function getAccessorColumnName()
    {
        return "user_id";
    }

    public function getAccessGovernor ()
    {
        return $this->getUser();
    }

    public function setAccessGovernor ($ag)
    {
        return $this->setUser($ag);
    }

    public function getAccessGovernorId ()
    {
        return $this->getUserId();
    }

    public function setAccessGovernorId ($id)
    {
        return $this->setUserId($id);
    }

// Attributes
    /**
     * @var integer
     *
     * @ORM\Column(name="user_id", type="integer")
     * @JMS\Groups({"default"})
     */
    protected $userId;

    /**
     * @ORM\ManyToOne(targetEntity="Carbon\ApiBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", nullable=false)
     * @JMS\Groups({"default"})
     */
    protected $user;

// Getters and setters
    /**
     * Gets the value of userId.
     *
     * @return integer
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Sets the value of userId.
     *
     * @param integer $userId the user id
     *
     * @return self
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * Gets the value of user.
     *
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Sets the value of user.
     *
     * @param mixed $user the user
     *
     * @return self
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }
}

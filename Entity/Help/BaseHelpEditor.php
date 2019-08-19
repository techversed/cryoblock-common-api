<?php

namespace Carbon\ApiBundle\Entity\Help;

use Carbon\ApiBundle\Annotation AS Carbon;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation AS JMS;
use Symfony\Component\Validator\Constraints as Assert;

/** @ORM\MappedSuperclass */
class BaseHelpEditor
{
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

    /**
     * @var integer
     *
     * @ORM\Column(name="help_id", type="integer")
     * @JMS\Groups({"default"})
     */
    protected $helpId;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Help\Help", inversedBy="helpEditors")
     * @ORM\JoinColumn(name="help_id", nullable=false)
     * @JMS\Groups({"default"})
     */
    protected $help;

    /**
     * @var \DateTime $deletedAt
     *
     * @ORM\Column(name="deleted_at", type="datetime", nullable=true)
     * @JMS\Groups({"default"})
     */
    protected $deletedAt;

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

    /**
     * Gets the value of helpId.
     *
     * @return integer
     */
    public function getHelpId()
    {
        return $this->helpId;
    }

    /**
     * Sets the value of helpId.
     *
     * @param integer $helpId the help id
     *
     * @return self
     */
    public function setHelpId($helpId)
    {
        $this->helpId = $helpId;

        return $this;
    }

    /**
     * Gets the value of help.
     *
     * @return mixed
     */
    public function getHelp()
    {
        return $this->help;
    }

    /**
     * Sets the value of help.
     *
     * @param mixed $help the help
     *
     * @return self
     */
    public function setHelp($help)
    {
        $this->help = $help;

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

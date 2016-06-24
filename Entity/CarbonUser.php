<?php

namespace Carbon\ApiBundle\Entity;

use Carbon\ApiBundle\Annotation AS Carbon;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping AS ORM;
use Doctrine\ORM\Mapping\MappedSuperclass;
use Uecode\Bundle\ApiKeyBundle\Entity\ApiKeyUser as BaseUser;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints AS Constraint;

/**
 * @MappedSuperclass
 * @UniqueEntity(
 *     fields={"email"},
 *     message="The email address provided is already associated with another account."
 * )
 * @UniqueEntity(
 *     fields={"username"},
 *     message="The username provided is already associated with another account."
 * )
 */
class CarbonUser extends BaseUser
{
    /**
     * The profile photo/avatar attachment
     *
     * @ORM\OneToOne(targetEntity="Carbon\ApiBundle\Entity\Attachment")
     * @ORM\JoinColumn(name="avatar_attachment_id", nullable=true)
     * @var Carbon\ApiBundle\Entity\Attachment
     */
    protected $avatarAttachment;

    /**
     * @ORM\Column(type="string", length=55)
     * @Constraint\NotNull(message="First name is required")
     * @Carbon\Searchable(name="firstName")
     *
     * @var string the users first name
     */
    protected $firstName;

    /**
     * @ORM\Column(type="string", length=55)
     * @Constraint\NotNull(message="Last name is required")
     * @Carbon\Searchable(name="lastName")
     *
     * @var string the users last name
     */
    protected $lastName;

    public function __construct()
    {
        parent::__construct();
        $this->roles = array();
    }

    public function setAvatarAttachment(Attachment $avatarAttachment)
    {
        $this->avatarAttachment = $avatarAttachment;
    }

    public function getAvatarAttachment()
    {
        return $this->avatarAttachment;
    }

    /**
     * Does the user have an avatar or not
     *
     * @return boolean
     */
    public function hasAvatar()
    {
        return NULL !== $this->avatarAttachment->getId();
    }

    /**
     * Set the users first name
     *
     * @param string $firstName
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    }

    /**
     * Get the users first name
     *
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * Set the users last name
     *
     * @param string $lastName
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
    }

    /**
     * Get the users last name
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * Get the users full name
     *
     * @return string
     */
    public function getFullName()
    {
        return $this->firstName . ' ' . $this->lastName;
    }
}

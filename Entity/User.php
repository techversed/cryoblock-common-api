<?php

namespace Carbon\ApiBundle\Entity;

use Carbon\ApiBundle\Annotation AS Carbon;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping AS ORM;
use Doctrine\ORM\Mapping\MappedSuperclass;
use JMS\Serializer\Annotation as JMS;
use Uecode\Bundle\ApiKeyBundle\Entity\ApiKeyUser as BaseUser;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints AS Constraint;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity()
 * @ORM\Table(name="cryoblock.cryoblock_user", schema="cryoblock")
 * @Gedmo\Loggable
 * @UniqueEntity(
 *     fields={"email"},
 *     message="The email address provided is already associated with another account."
 * )
 * @UniqueEntity(
 *     fields={"username"},
 *     message="The username provided is already associated with another account."
 * )
 */
class User extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Project\ProjectEditor", mappedBy="user")
     */
    protected $userProjects;

    /**
     * @ORM\OneToMany(targetEntity="Carbon\ApiBundle\Entity\UserGroup", mappedBy="user")
     */
    protected $userGroups;

    /**
     * The profile photo/avatar attachment
     *
     * @ORM\OneToOne(targetEntity="Carbon\ApiBundle\Entity\Attachment")
     * @ORM\JoinColumn(name="avatar_attachment_id", nullable=true)
     * @var Carbon\ApiBundle\Entity\Attachment
     */
    protected $avatarAttachment;

    /**
     * @ORM\Column(type="string", length=55, nullable=true)
     * @Carbon\Searchable(name="firstName")
     * @Gedmo\Versioned
     *
     * @var string the users first name
     */
    protected $firstName;

    /**
     * @ORM\Column(type="string", length=55, nullable=true)
     * @Carbon\Searchable(name="lastName")
     * @Gedmo\Versioned
     *
     * @var string the users last name
     */
    protected $lastName;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Carbon\Searchable(name="title")
     * @Gedmo\Versioned
     * @JMS\Groups({"default"})
     *
     * @var string the users title (research interests)
     */
    protected $title;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Carbon\Searchable(name="about")
     * @Gedmo\Versioned
     * @JMS\Groups({"default"})
     *
     * @var string the users about (research interests)
     */
    protected $about;

    /**
     * @Carbon\Searchable(name="username")
     * @Gedmo\Versioned
     */
    protected $username;

    /**
     * @Carbon\Searchable(name="email")
     * @Gedmo\Versioned
     */
    protected $email;

    /**
     * @var \DateTime $created
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="created_at", type="datetime")
     * @JMS\Groups({"default"})
     */
    protected $createdAt;

    /**
     * @var \DateTime $updated
     *
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(name="updated_at", type="datetime")
     * @JMS\Groups({"default"})
     */
    protected $updatedAt;

// VIOLATION -- THIS SHOULD REALLY NOT BE HANDLED IN COMMON
    /**
     * @var integer ClonedSampleId
     * @ORM\Column(name="cloned_sample_id", type="integer", nullable= true)
     * @JMS\Groups({"default"})
     */
    protected $clonedSampleId;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Storage\Sample")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="cloned_sample_id", referencedColumnName="id")
     * })
     * @JMS\Groups({"default"})
     */
    protected $clonedSample;

    /**
     * @Gedmo\Versioned
     */
    protected $enabled;
    protected $roles = array();
    public $groups;

    public function __construct()
    {
        parent::__construct();
        $this->roles = array();
        $this->userGroups = new ArrayCollection();
    }

    /**
     * Set id
     *
     * @param integer $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Returns the user roles
     *
     * @JMS\VirtualProperty()
     *
     * @return array The roles
     */
    public function getRoles()
    {
        $roles = $this->roles;

        foreach ($this->userGroups as $userGroup) {

            if ($groupRoles = $userGroup->getGroup()->getRoles()) {

                $roles = array_merge($roles, array_map(function ($role) {
                    return $role->getRole();
                }, $groupRoles));
            }
        }

        return array_unique($roles);
    }

    /**
     * @JMS\VirtualProperty()
     * @JMS\Groups({"default"})
     */
    public function getStringLabel()
    {
        return $this->getFullName();
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

    /**
     * Set the users created at
     *
     * @param string $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * Get the users created at
     *
     * @return string
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set the users last name
     *
     * @param string $updatedAt
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * Get the users last name
     *
     * @return string
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Gets the value of title.
     *
     * @return string the users title (research interests)
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Sets the value of title.
     *
     * @param string the users title (research interests) $title the title
     *
     * @return self
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Gets the value of about.
     *
     * @return string the users about (research interests)
     */
    public function getAbout()
    {
        return $this->about;
    }

    /**
     * Sets the value of about.
     *
     * @param string the users about (research interests) $about the about
     *
     * @return self
     */
    public function setAbout($about)
    {
        $this->about = $about;

        return $this;
    }


    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getUserGroups()
    {
        return $this->userGroups;
    }

    /**
     * @param mixed $userGroups
     *
     * @return self
     */
    public function setUserGroups($userGroups)
    {
        $this->userGroups = $userGroups;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param mixed $username
     *
     * @return self
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     *
     * @return self
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return integer ClonedSampleId
     */
    public function getClonedSampleId()
    {
        return $this->clonedSampleId;
    }

    /**
     * @param integer ClonedSampleId $clonedSampleId
     *
     * @return self
     */
    public function setClonedSampleId($clonedSampleId)
    {
        $this->clonedSampleId = $clonedSampleId;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getClonedSample()
    {
        return $this->clonedSample;
    }

    /**
     * @param mixed $clonedSample
     *
     * @return self
     */
    public function setClonedSample($clonedSample)
    {
        $this->clonedSample = $clonedSample;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * @param mixed $enabled
     *
     * @return self
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * @param mixed $groups
     *
     * @return self
     */
    public function setGroups($groups)
    {
        $this->groups = $groups;

        return $this;
    }


    /**
     * Gets the value of userProjects.
     *
     * @return mixed
     */
    public function getUserProjects()
    {
        return $this->userProjects;
    }

    /**
     * Sets the value of userProjects.
     *
     * @param mixed $userProjects the user projects
     *
     * @return self
     */
    public function setUserProjects($userProjects)
    {
        $this->userProjects = $userProjects;

        return $this;
    }

}

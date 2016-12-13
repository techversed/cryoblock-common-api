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
 * @ORM\Table(name="user")
 * @JMS\ExclusionPolicy("all")
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
     * @ORM\GeneratedValue
     * @JMS\Expose()
     * @JMS\Groups("default")
     */
    protected $id;

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
     *
     * @var string the users first name
     */
    protected $firstName;

    /**
     * @ORM\Column(type="string", length=55, nullable=true)
     * @Carbon\Searchable(name="lastName")
     *
     * @var string the users last name
     */
    protected $lastName;

    /**
     * @Carbon\Searchable(name="username")
     */
    protected $username;

    /**
     * @Carbon\Searchable(name="email")
     */
    protected $email;

    /**
     * @var \DateTime $created
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="created_at", type="datetime", nullable=true)
     * @JMS\Groups({"default"})
     */
    private $createdAt;

    /**
     * @var \DateTime $updated
     *
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(name="updated_at", type="datetime", nullable=true)
     * @JMS\Groups({"default"})
     */
    private $updatedAt;

    protected $roles = array();

    public function __construct()
    {
        parent::__construct();
        $this->roles = array();
        $this->userGroups = new ArrayCollection();
    }

    /**
     * Returns the user roles
     *
     * @JMS\VirtualProperty()
     * @JMS\Groups("default")
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
}

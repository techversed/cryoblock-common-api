<?php

namespace Carbon\ApiBundle\Entity\Storage;

use Carbon\ApiBundle\Annotation AS Carbon;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation AS JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\MappedSuperclass
 */
class BaseCatalog
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
     * @var User $createdBy
     *
     * @Gedmo\Blameable(on="create")
     * @ORM\ManyToOne(targetEntity="Carbon\ApiBundle\Entity\User")
     * @ORM\JoinColumn(name="created_by_id", referencedColumnName="id")
     * @JMS\Groups({"default"})
     */
    protected $createdBy;

    /**
     * Created by id
     * @ORM\Column(name="created_by_id", type="integer", nullable=false)
     * @JMS\Groups({"default"})
     */
    protected $createdById;

    /**
     * @var User $updatedBy
     *
     * @Gedmo\Blameable(on="update")
     * @ORM\ManyToOne(targetEntity="Carbon\ApiBundle\Entity\User")
     * @ORM\JoinColumn(name="updated_by_id", referencedColumnName="id")
     * @JMS\Groups({"default"})
     * @JMS\MaxDepth(1)
     */
    protected $updatedBy;

    /**
     * Created by id
     * @ORM\Column(name="updated_by_id", type="integer", nullable=false)
     * @JMS\Groups({"default"})
     */
    protected $updatedById;

    /**
     * @var \DateTime $updated
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     * @JMS\Groups({"default"})
     */
    protected $createdAt;

    /**
     * @var \DateTime $updated
     *
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime")
     * @JMS\Groups({"default"})
     */
    protected $updatedAt;

    /**
     * @var \DateTime $deletedAt
     *
     * @ORM\Column(name="deleted_at", type="datetime", nullable=true)
     * @Gedmo\Versioned
     * @JMS\Groups({"default"})
     */
    protected $deletedAt;

    /**
     * @var string $status
     *
     * @ORM\Column(name="status", type="string", nullable=false)
     * @JMS\Groups({"default"})
     * @Gedmo\Versioned
     */
    protected $status;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Storage\ParentCatalog", mappedBy="childCatalog")
     */
    protected $parentCatalogs;

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
     * Gets the value of createdBy.
     *
     * @return User $createdBy
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * Sets the value of createdBy.
     *
     * @param User $createdBy $createdBy the created by
     *
     * @return self
     */
    public function setCreatedBy($createdBy)
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * Gets the Created by id.
     *
     * @return mixed
     */
    public function getCreatedById()
    {
        return $this->createdById;
    }

    /**
     * Sets the Created by id.
     *
     * @param mixed $createdById the created by id
     *
     * @return self
     */
    public function setCreatedById($createdById)
    {
        $this->createdById = $createdById;

        return $this;
    }

    /**
     * Gets the value of updatedBy.
     *
     * @return User $updatedBy
     */
    public function getUpdatedBy()
    {
        return $this->updatedBy;
    }

    /**
     * Sets the value of updatedBy.
     *
     * @param User $updatedBy $updatedBy the updated by
     *
     * @return self
     */
    public function setUpdatedBy($updatedBy)
    {
        $this->updatedBy = $updatedBy;

        return $this;
    }

    /**
     * Gets the Created by id.
     *
     * @return mixed
     */
    public function getUpdatedById()
    {
        return $this->updatedById;
    }

    /**
     * Sets the Created by id.
     *
     * @param mixed $updatedById the updated by id
     *
     * @return self
     */
    public function setUpdatedById($updatedById)
    {
        $this->updatedById = $updatedById;

        return $this;
    }

    /**
     * Gets the value of updatedAt.
     *
     * @return \DateTime $updated
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Sets the value of updatedAt.
     *
     * @param \DateTime $updated $updatedAt the updated at
     *
     * @return self
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

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
}

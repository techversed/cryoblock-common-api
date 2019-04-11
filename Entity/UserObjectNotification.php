<?php

namespace Carbon\ApiBundle\Entity;

use Carbon\ApiBundle\Entity\BaseCryoblockEntity;
use Carbon\ApiBundle\Annotation AS Carbon;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation AS JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity()
 * @ORM\Table(name="cryoblock.user_object_notification", schema="cryoblock")
 */
class UserObjectNotification extends BaseCryoblockEntity
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @JMS\Groups({"default"})
     */
    protected $id;

    /**
     * @ORM\Column(name="entity_id", type="integer", nullable=true)
     * @JMS\Groups({"default"})
     */
    protected $entityId;


    // Has the user chosen to stop recieving updates on this item?
    /**
     * @var boolean dismissed
     *
     * @ORM\Column(name="dismissed", type="boolean", nullable=false, options={"default": false})
     * @JMS\Groups({"default"})
     */
    protected $dismissed = false;

    /**
     * @ORM\ManyToOne(targetEntity="Carbon\ApiBundle\Entity\EntityDetail")
     * @ORM\JoinColumn(name="entity_detail_id", referencedColumnName="id")
     * @JMS\Groups({"default", "notifications"})
     */
    protected $entityDetail;

    /**
     * @ORM\Column(name="entity_detail_id", type="integer", nullable=false)
     * @JMS\Groups({"default"})
     */
    protected $entityDetailId;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="Carbon\ApiBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * @JMS\Groups({"default"})
     */
    protected $user;

    /**
     * @ORM\Column(name="user_id", type="integer", nullable=false)
     * @JMS\Groups({"default"})
     */
    protected $userId;

    /**
     * @ORM\Column(nullable=true, type="boolean")
     * @JMS\Groups("default")
     */
    protected $onCreate = false;

    /**
     * @ORM\Column(nullable=true, type="boolean")
     * @JMS\Groups("default")
     */
    protected $onUpdate = false;

    /**
     * @ORM\Column(nullable=true, type="boolean")
     * @JMS\Groups("default")
     */
    protected $onDelete = false;

    /* Transient */

    // This is not kept in the database at any point --  can't have a many to many because there is not a single table which the foreign key references -- there are a number of tables.
    // When fetching user object notifications in the controller this should be populated automatically.
    // DO NOT ADD AN ORM MAPPING TO THIS....

    /**
     * @JMS\Groups({"default", "notifications"})
     * @JMS\MaxDepth(2)
     */
    protected $entity;

    /**
     * @return mixed
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * @param mixed $entity
     * @return self
     */
    public function setEntity($entity)
    {
        $this->entity = $entity;
        return $this;
    }

    /**
     * Gets the value of id.
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets the value of id.
     *
     * @param mixed $id the id
     *
     * @return self
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Gets the value of entityId.
     *
     * @return mixed
     */
    public function getEntityId()
    {
        return $this->entityId;
    }

    /**
     * Sets the value of entityId.
     *
     * @param mixed $entityId the entity id
     *
     * @return self
     */
    public function setEntityId($entityId)
    {
        $this->entityId = $entityId;

        return $this;
    }

    /**
     * Gets the value of entityDetail.
     *
     * @return mixed
     */
    public function getEntityDetail()
    {
        return $this->entityDetail;
    }

    /**
     * Sets the value of entityDetail.
     *
     * @param mixed $entityDetail the entity detail
     *
     * @return self
     */
    public function setEntityDetail($entityDetail)
    {
        $this->entityDetail = $entityDetail;

        return $this;
    }

    /**
     * Gets the value of entityDetailId.
     *
     * @return mixed
     */
    public function getEntityDetailId()
    {
        return $this->entityDetailId;
    }

    /**
     * Sets the value of entityDetailId.
     *
     * @param mixed $entityDetailId the entity detail id
     *
     * @return self
     */
    public function setEntityDetailId($entityDetailId)
    {
        $this->entityDetailId = $entityDetailId;

        return $this;
    }

    /**
     * Gets the value of user.
     *
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Sets the value of user.
     *
     * @param User $user the user
     *
     * @return self
     */
    public function setUser(User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Gets the value of userId.
     *
     * @return mixed
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Sets the value of userId.
     *
     * @param mixed $userId the user id
     *
     * @return self
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * Gets the value of onCreate.
     *
     * @return mixed
     */
    public function getOnCreate()
    {
        return $this->onCreate;
    }

    /**
     * Sets the value of onCreate.
     *
     * @param mixed $onCreate the on create
     *
     * @return self
     */
    public function setOnCreate($onCreate)
    {
        $this->onCreate = $onCreate;

        return $this;
    }

    /**
     * Gets the value of onUpdate.
     *
     * @return mixed
     */
    public function getOnUpdate()
    {
        return $this->onUpdate;
    }

    /**
     * Sets the value of onUpdate.
     *
     * @param mixed $onUpdate the on update
     *
     * @return self
     */
    public function setOnUpdate($onUpdate)
    {
        $this->onUpdate = $onUpdate;

        return $this;
    }

    /**
     * Gets the value of onDelete.
     *
     * @return mixed
     */
    public function getOnDelete()
    {
        return $this->onDelete;
    }

    /**
     * Sets the value of onDelete.
     *
     * @param mixed $onDelete the on delete
     *
     * @return self
     */
    public function setOnDelete($onDelete)
    {
        $this->onDelete = $onDelete;

        return $this;
    }

    /**
     * @return boolean dismissed
     */
    public function isDismissed()
    {
        return $this->dismissed;
    }

    /**
     * @param boolean dismissed $dismissed
     *
     * @return self
     */
    public function setDismissed($dismissed)
    {
        $this->dismissed = $dismissed;

        return $this;
    }

    /**
     * @return boolean dismissed
     */
    public function getDismissed()
    {
        return $this->dismissed;
    }
}

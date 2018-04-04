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
     * @var string
     *
     * @ORM\Column(name="entity", type="string", length=300)
     * @JMS\Groups({"default"})
     * @Assert\NotBlank()
     */
    protected $entity;

    /**
     * @ORM\Column(name="entity_id", type="integer", nullable=true)
     * @JMS\Groups({"default"})
     */
    protected $entityId;

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
     * @var string
     *
     * @ORM\Column(name="url", type="string", length=300)
     * @JMS\Groups({"default"})
     * @Assert\NotBlank()
     */
    protected $url;

    /**
     * @var string
     *
     * @ORM\Column(name="object_description", type="string")
     * @JMS\Groups({"default"})
     * @Assert\NotBlank()
     */
    protected $objectDescription;

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
     * Gets the value of entity.
     *
     * @return string
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * Sets the value of entity.
     *
     * @param string $entity the entity
     *
     * @return self
     */
    public function setEntity($entity)
    {
        $this->entity = $entity;

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
     * Gets the Created by id.
     *
     * @return mixed
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Sets the Created by id.
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
     * Gets the value of url.
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Sets the value of url.
     *
     * @param string $url the url
     *
     * @return self
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Gets the value of objectDescription.
     *
     * @return string
     */
    public function getObjectDescription()
    {
        return $this->objectDescription;
    }

    /**
     * Sets the value of objectDescription.
     *
     * @param string $objectDescription the object description
     *
     * @return self
     */
    public function setObjectDescription($objectDescription)
    {
        $this->objectDescription = $objectDescription;

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
}

<?php

namespace Carbon\ApiBundle\Entity;

use Carbon\ApiBundle\Entity\BaseCryoblockEntity;
use Carbon\ApiBundle\Annotation AS Carbon;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation AS JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity()
 * @ORM\Table(name="cryoblock.group_object_notification", schema="cryoblock")
 */
class GroupObjectNotification extends BaseCryoblockEntity
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
     * @var Group $group
     *
     * @ORM\ManyToOne(targetEntity="Carbon\ApiBundle\Entity\Group")
     * @ORM\JoinColumn(name="on_create_group_id", referencedColumnName="id")
     * @JMS\Groups({"default"})
     */
    protected $onCreateGroup;

    /**
     * @var Group $group
     *
     * @ORM\ManyToOne(targetEntity="Carbon\ApiBundle\Entity\Group")
     * @ORM\JoinColumn(name="on_update_group_id", referencedColumnName="id")
     * @JMS\Groups({"default"})
     */
    protected $onUpdateGroup;

    /**
     * @var Group $group
     *
     * @ORM\ManyToOne(targetEntity="Carbon\ApiBundle\Entity\Group")
     * @ORM\JoinColumn(name="on_delete_group_id", referencedColumnName="id")
     * @JMS\Groups({"default"})
     */
    protected $onDeleteGroup;

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
     * Gets the value of onCreateGroup.
     *
     * @return Group $group
     */
    public function getOnCreateGroup()
    {
        return $this->onCreateGroup;
    }

    /**
     * Sets the value of onCreateGroup.
     *
     * @param Group $group $onCreateGroup the on create group
     *
     * @return self
     */
    public function setOnCreateGroup($onCreateGroup)
    {
        $this->onCreateGroup = $onCreateGroup;

        return $this;
    }

    /**
     * Gets the value of onUpdateGroup.
     *
     * @return Group $group
     */
    public function getOnUpdateGroup()
    {
        return $this->onUpdateGroup;
    }

    /**
     * Sets the value of onUpdateGroup.
     *
     * @param Group $group $onUpdateGroup the on update group
     *
     * @return self
     */
    public function setOnUpdateGroup($onUpdateGroup)
    {
        $this->onUpdateGroup = $onUpdateGroup;

        return $this;
    }

    /**
     * Gets the value of onDeleteGroup.
     *
     * @return Group $group
     */
    public function getOnDeleteGroup()
    {
        return $this->onDeleteGroup;
    }

    /**
     * Sets the value of onDeleteGroup.
     *
     * @param Group $group $onDeleteGroup the on delete group
     *
     * @return self
     */
    public function setOnDeleteGroup($onDeleteGroup)
    {
        $this->onDeleteGroup = $onDeleteGroup;

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
}

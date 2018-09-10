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
     * @ORM\ManyToOne(targetEntity="Carbon\ApiBundle\Entity\EntityDetail")
     * @ORM\JoinColumn(name="entity_detail_id", referencedColumnName="id")
     * @JMS\Groups({"default"})
     */
    protected $entityDetail;

    /**
     * @ORM\Column(name="entity_detail_id", type="integer", nullable=false)
     * @JMS\Groups({"default"})
     */
    protected $entityDetailId;

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
}

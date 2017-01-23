<?php

namespace Carbon\ApiBundle\Entity;

use Carbon\ApiBundle\Annotation AS Carbon;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation AS JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity()
 * @ORM\Table(name="cryoblock.object_notification", schema="cryoblock")
 */
class ObjectNotification
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
     * @ORM\Column(name="object_type", type="string", length=300)
     * @JMS\Groups({"default"})
     * @Assert\NotBlank()
     */
    protected $objectType;

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

    public function __construct()
    {
    }

    public function getId()
    {
        return $this->id;
    }

    public function getNotificationType()
    {
        return $this->notificationType;
    }

    public function setObjectType($objectType)
    {
        $this->objectType = $objectType;
    }

    public function getObjectType()
    {
        return $this->objectType;
    }

    public function setOnCreateGroup($onCreateGroup)
    {
        $this->onCreateGroup = $onCreateGroup;
    }

    public function getOnCreateGroup()
    {
        return $this->onCreateGroup;
    }

    public function setOnUpdateGroup($onUpdateGroup)
    {
        $this->onUpdateGroup = $onUpdateGroup;
    }

    public function getOnUpdateGroup()
    {
        return $this->onUpdateGroup;
    }

    public function setOnDeleteGroup($onDeleteGroup)
    {
        $this->onDeleteGroup = $onDeleteGroup;
    }

    public function getOnDeleteGroup()
    {
        return $this->onDeleteGroup;
    }
}

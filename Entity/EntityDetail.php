<?php

namespace Carbon\ApiBundle\Entity;

use Carbon\ApiBundle\Annotation AS Carbon;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping AS ORM;
use Doctrine\ORM\Mapping\MappedSuperclass;
use JMS\Serializer\Annotation as JMS;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints AS Constraint;
use Gedmo\Mapping\Annotation as Gedmo;
use Carbon\ApiBundle\Entity\BaseCryoblockEntity;

/**
 * @ORM\Entity()
 * @ORM\Table(name="cryoblock.entity_detail", schema="cryoblock")
 * @Gedmo\Loggable
 * @Gedmo\SoftDeleteable(fieldName= "deletedAt", timeAware=false)
 * @UniqueEntity(
 *     fields={"objectClassName"},
 *     message="There should only be one entry for each type of object"
 * )
 */
class EntityDetail extends BaseCryoblockEntity
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
     * @ORM\Column(name="object_class_name", type="string", nullable=false)
     * @JMS\Groups({"default"})
     */
    protected $objectClassName;

    /**
     * @var string
     *
     * @ORM\Column(name="object_description", type="string", nullable=false)
     * @JMS\Groups({"default"})
     */
    protected $objectDescription;

    /**
     * @var string
     *
     * @ORM\Column(name="object_url", type="string", nullable=false)
     * @JMS\Groups({"default"})
     */
    protected $objectUrl;

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
     * Gets the value of objectClassName.
     *
     * @return string
     */
    public function getObjectClassName()
    {
        return $this->objectClassName;
    }

    /**
     * Sets the value of objectClassName.
     *
     * @param string $objectClassName the object class name
     *
     * @return self
     */
    public function setObjectClassName($objectClassName)
    {
        $this->objectClassName = $objectClassName;

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
     * Gets the value of objectUrl.
     *
     * @return string
     */
    public function getObjectUrl()
    {
        return $this->objectUrl;
    }

    /**
     * Sets the value of objectUrl.
     *
     * @param string $objectUrl the object url
     *
     * @return self
     */
    public function setObjectUrl($objectUrl)
    {
        $this->objectUrl = $objectUrl;

        return $this;
    }
}

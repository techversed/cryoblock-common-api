<?php

namespace Carbon\ApiBundle\Entity;

use Carbon\ApiBundle\Annotation AS Carbon;
use Doctrine\ORM\Mapping AS ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints AS Constraint;

/*

    Notes on future changes:
        - Soft deleteable does not seem to be enabled but there is a deleted at column.
        - "deletedAt" should be "deleted_at" in the orm column annotation - if we want to adhere to the convention that we established previously.

*/

/**
 * @ORM\Entity()
 * @ORM\Table(
 *     schema="cryoblock",
 *     name="cryoblock.attachment",
 * )
 *
 * The entity model for an object attachment
 *
 * @version 1.01
 * @author Andre Jon Branchizio <andrejbranch@gmail.com>
 */
class Attachment
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @JMS\Groups({"default"})
     * @var int the attachment id
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=500)
     * @JMS\Groups({"default"})
     *
     * @var string the name of the original file
     */
    private $name;

    /**
     * @var string $objectId
     *
     * @ORM\Column(name="object_id", length=64, nullable=true)
     * @JMS\Groups({"default"})
     */
    protected $objectId;

    /**
     * @var string $objectClass
     *
     * @ORM\Column(name="object_class", type="string", length=500, nullable=true)
     * @JMS\Groups({"default"})
     */
    protected $objectClass;

    /**
     * @ORM\Column(type="string", length=500)
     * @JMS\Groups({"default"})
     *
     * @var string path to the file for downloads
     */
    private $downloadPath;

    /**
     * @ORM\Column(type="string")
     * @JMS\Groups({"default"})
     *
     * @var string mime type of the attachment
     */
    private $mimeType;

    /**
     * @ORM\Column(type="integer")
     * @JMS\Groups({"default"})
     *
     * @var int attachments file size in bytes
     */
    private $size;

    /**
     * @JMS\Groups({"default"})
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="update")
     *
     * @var \DateTime $updated
     */
    private $updatedAt;

    //The column namne should be deleted_at -- may want to fix this when we get a chance.
    /**
     * @JMS\Groups({"default"})
     * @ORM\Column(name="deletedAt", type="datetime", nullable=true)
     */
    private $deletedAt;

    /**
     * Set the attachment id
     *
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Get the attachment id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the attachments original file name
     *
     * @param type $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Get the attachments original file name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the attachments download path
     *
     * @param type $name
     */
    public function setDownloadPath($downloadPath)
    {
        $this->downloadPath = $downloadPath;
    }

    /**
     * Get the attachments download path
     *
     * @return string
     */
    public function getDownloadPath()
    {
        return $this->downloadPath;
    }

    /**
     * Set the attachments mime type
     *
     * @param string $mimeType
     */
    public function setMimeType($mimeType)
    {
        $this->mimeType = $mimeType;
    }

    /**
     * Get the attachments mime type
     *
     * @return string
     */
    public function getMimeType()
    {
        return $this->mimeType;
    }

    /**
     * Set the attachments file size
     *
     * @param int $size
     */
    public function setSize($size)
    {
        $this->size = (int) $size;
    }

    /**
     * Get the attachments file size
     *
     * @return int
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * Gets the value of objectId.
     *
     * @return string $objectId
     */
    public function getObjectId()
    {
        return $this->objectId;
    }

    /**
     * Sets the value of objectId.
     *
     * @param string $objectId $objectId the object id
     *
     * @return self
     */
    public function setObjectId($objectId)
    {
        $this->objectId = $objectId;

        return $this;
    }

    /**
     * Gets the value of objectClass.
     *
     * @return string $objectClass
     */
    public function getObjectClass()
    {
        return $this->objectClass;
    }

    /**
     * Sets the value of objectClass.
     *
     * @param string $objectClass $objectClass the object class
     *
     * @return self
     */
    public function setObjectClass($objectClass)
    {
        $this->objectClass = $objectClass;

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
     * @return mixed
     */
    public function getDeletedAt()
    {
        return $this->deletedAt;
    }

    /**
     * Sets the value of deletedAt.
     *
     * @param mixed $deletedAt the deleted at
     *
     * @return self
     */
    public function setDeletedAt($deletedAt)
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }
}

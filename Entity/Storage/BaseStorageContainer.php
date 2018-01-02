<?php

namespace Carbon\ApiBundle\Entity\Storage;

use JMS\Serializer\Annotation AS JMS;
use Carbon\ApiBundle\Annotation AS Carbon;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/** @ORM\MappedSuperclass */
class BaseStorageContainer
{
    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     * @JMS\Groups({"default"})
     * @Carbon\Searchable(name="name")
     */
    private $name;

    /**
     * Set name
     *
     * @param string $name
     *
     * @return StorageContainer
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
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

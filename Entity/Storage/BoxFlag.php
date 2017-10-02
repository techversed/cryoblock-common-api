<?php

namespace Carbon\ApiBundle\Entity;

use Carbon\ApiBundle\Annotation AS Carbon;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation AS JMS;
use Symfony\Component\Security\Core\Description\DescriptionInterface;

/**
 * @ORM\Entity()
 * @ORM\Table(name="storage.box_flag", schema="storage")
 */
class BoxFlag
{
    /**
     * @ORM\name
     * @ORM\Column(type="string")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @JMS\Groups({"default"})
     */
    protected $name;

    /**
     * @ORM\Column(type="string", name="description", unique=true)
     * @Carbon\Searchable(name="description")
     * @JMS\Groups({"default"})
     */
    protected $description;

    /**
     * Populate the description field
     *
     * @param string $description ROLE_FOO etc
     */
    public function __construct($description)
    {
        $this->description = $description;
    }
    public function __toString()
    {
        return $this->description;
    }
    /**
     * Return the description field.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }
    /**
     * @JMS\VirtualProperty()
     * @JMS\Groups({"default"})
     */
    public function getStringLabel()
    {
        return $this->id . ': ' . $this->getDescription();
    }
}

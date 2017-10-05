<?php

namespace Carbon\ApiBundle\Entity\Storage;

use Carbon\ApiBundle\Annotation AS Carbon;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation AS JMS;
use Symfony\Component\Security\Core\Description\DescriptionInterface;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity()
 * @ORM\Table(name="storage.box_flag", schema="storage")
 */
class BoxFlag
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @JMS\Groups({"default"})
     */
    protected $id;

    /**
     * @ORM\Column(type="string", name="name")
     * @Carbon\Searchable(name="name")
     * @JMS\Groups({"default"})
     */
    protected $name;

    /**
     * @ORM\Column(type="string", name="description")
     * @Carbon\Searchable(name="description")
     * @JMS\Groups({"default"})
     */
    protected $description;

    public function __toString()
    {
        return $this->name;
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
        return $this->id . ': ' . $this->getName();
    }
}

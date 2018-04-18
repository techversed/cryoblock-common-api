<?php

namespace Carbon\ApiBundle\Entity\Storage;

use JMS\Serializer\Annotation AS JMS;
use Carbon\ApiBundle\Annotation AS Carbon;
use Carbon\ApiBundle\Entity\BaseCryoblockEntity;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/** @ORM\MappedSuperclass */
class BaseTag extends BaseCryoblockEntity
{
    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     * @JMS\Groups({"default"})
     * @Carbon\Searchable(name="name")
     * @Gedmo\Versioned
     */
    protected $name;

    /**
     * @ORM\Column(nullable=true)
     * @JMS\Groups("default")
     * @Carbon\Searchable(name="description")
     * @Gedmo\Versioned
     */
    protected $description;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Storage\Sample", mappedBy="tag")
     */
    protected $tagSamples;

    /**
     * Set name
     *
     * @param string $name
     * @return Tag
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

    /**
     * Gets the value of description.
     *
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Sets the value of description.
     *
     * @param mixed $description the description
     *
     * @return self
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Gets the value of tagSamples.
     *
     * @return mixed
     */
    public function getTagSamples()
    {
        return $this->tagSamples;
    }

    /**
     * Sets the value of tagSamples.
     *
     * @param mixed $tagSamples the tag samples
     *
     * @return self
     */
    public function setTagSamples($tagSamples)
    {
        $this->tagSamples = $tagSamples;

        return $this;
    }
}

<?php

namespace Carbon\ApiBundle\Entity\Storage;

use JMS\Serializer\Annotation AS JMS;
use Carbon\ApiBundle\Annotation AS Carbon;
use Carbon\ApiBundle\Entity\BaseCryoblockEntity;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/** @ORM\MappedSuperclass */
abstract class BaseTag extends BaseCryoblockEntity
{
    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, unique=true)
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
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Storage\SampleTag", mappedBy="tag")
     * @JMS\Groups({"children"})
     */
    protected $sampleTags;

    /**
     * @JMS\Groups({"default"})
     */
    public $samples;

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
     * Gets the value of sampleTags.
     *
     * @return mixed
     */
    public function getSampleTags()
    {
        return $this->sampleTags;
    }

    /**
     * Sets the value of sampleTags.
     *
     * @param mixed $sampleTags the sample tags
     *
     * @return self
     */
    public function setSampleTags($sampleTags)
    {
        $this->sampleTags = $sampleTags;

        return $this;
    }

    /**
     * Gets the value of samples.
     *
     * @return mixed
     */
    public function getSamples()
    {
        return $this->samples;
    }

    /**
     * Sets the value of samples.
     *
     * @param mixed $samples the samples
     *
     * @return self
     */
    public function setSamples($samples)
    {
        $this->samples = $samples;

        return $this;
    }
}

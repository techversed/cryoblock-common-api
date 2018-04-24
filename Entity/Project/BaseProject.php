<?php
namespace Carbon\ApiBundle\Entity\Project;

use Carbon\ApiBundle\Annotation As Carbon;
use Carbon\ApiBundle\Entity\BaseCryoblockEntity;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation AS JMS;
use Symfony\Component\Validator\Constraints as Assert;


/** @ORM\MappedSuperclass */
class BaseProject extends BaseCryoblockEntity
{


    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     * @JMS\Groups({"default"})
     * @Carbon\Searchable(name="name")
     */
    protected $name;

    //createdBy, createdBiId, updatedBy, updatedById, createdAt, updatedAt, deletedAt in BaseCryoblockEntity

    //I DON'T KNOW WHAT IS NEEDED HERE...
    //Many to many on samples
    //Many to many on pipeline
    //Many to many on users
    //One to many on requests
    //Many to one on project lead
    //group that contains the members who are part of this project
    //One to Many on grants
    //One to Many on reports
    //MTA
    //VIM
    //CDA

    //May want a smaller unit than project -- such as objective... You could allocate the resources that belong to the project to the series of objectives that sit below it...
        //Example -- you could create an objective for performing crystallography and you could then reserve samples for that purpose...


    /**
     * @JMS\VirtualProperty()
     * @JMS\Groups({"default"})
     */
    public function getStringLabel()
    {
        return $this->getName();
    }

    /**
     * Gets the value of name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets the value of name.
     *
     * @param string $name the name
     *
     * @return self
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }
}

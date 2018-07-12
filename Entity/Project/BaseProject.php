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

//createdBy, createdById, updatedBy, updatedById, createdAt, updatedAt, deletedAt in BaseCryoblockEntity

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     * @JMS\Groups({"default"})
     * @Carbon\Searchable(name="name")
     */
    protected $name;

    /**
     * Valid Project Statuses
     *
     * @var array
    */
    protected $validStatuses = array(
        'Ongoing',
        'Completed'
    );

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text")
     * @JMS\Groups({"default"})
     * @Carbon\Searchable(name="description")
     * @Gedmo\Versioned
    */
    protected $description;

    /**
     * @var string
     *
     * @Carbon\Searchable(name="status")
     * @ORM\Column(name="status", type="string", length=255)
     * @JMS\Groups({"default"})
     * @Gedmo\Versioned
     */
    protected $status;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Project\ProjectSample", mappedBy="project")
     */
    protected $projectSamples;

    public $samples;

    // /**
    //  * @ORM\OneToMany(targetEntity="AppBundle\Entity\Production\Dna\RequestProject", mappedBy="project")
    //  */
    // protected $projectDnaRequests;

    // public $dnaRequests;

    // /**
    //  * @ORM\OneToMany(targetEntity="AppBundle\Entity\Production\HumanSpecimen\RequestProject", mappedBy="project")
    //  */
    // protected $projectHumanSpecimenRequests;

    // public $humanSpecimenRequests;

    // /**
    //  * @ORM\OneToMany(targetEntity="AppBundle\Entity\Production\Pbmc\RequestProject", mappedBy="project")
    //  */
    // protected $projectPbmcRequests;

    // public $pbmcRequests;

    // /**
    //  * @ORM\OneToMany(targetEntity="AppBundle\Entity\Production\ProteinExpression\RequestProject", mappedBy="project")
    //  */
    // protected $projectProteinExpressionRequests;

    // public $proteinExpressionRequests;

    // /**
    //  * @ORM\OneToMany(targetEntity="AppBundle\Entity\Production\ProteinPurification\RequestProject", mappedBy="project")
    //  */
    // protected $projectProteinPurificationRequests;

    // public $proteinPurificationRequests;

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

    /**
     * Gets the Valid Project Statuses.
     *
     * @return array
     */
    public function getValidStatuses()
    {
        return $this->validStatuses;
    }

    /**
     * Sets the Valid Project Statuses.
     *
     * @param array $validStatuses the valid statuses
     *
     * @return self
     */
    public function setValidStatuses(array $validStatuses)
    {
        $this->validStatuses = $validStatuses;

        return $this;
    }

    /**
     * Gets the value of description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Sets the value of description.
     *
     * @param string $description the description
     *
     * @return self
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Gets the value of projectSamples.
     *
     * @return mixed
     */
    public function getProjectSamples()
    {
        return $this->projectSamples;
    }

    /**
     * Sets the value of projectSamples.
     *
     * @param mixed $projectSamples the project samples
     *
     * @return self
     */
    public function setProjectSamples($projectSamples)
    {
        $this->projectSamples = $projectSamples;

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

    /**
     * Gets the value of status.
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Sets the value of status.
     *
     * @param string $status the status
     *
     * @return self
     */
    public function setStatus($status)
    {
        if (in_array($status, $this->validStatuses)) {
            $this->status = $status;
        }
        return $this;
    }

    // /**
    //  * Gets the value of projectRequests.
    //  *
    //  * @return mixed
    //  */
    // public function getProjectRequests()
    // {
    //     return $this->projectRequests;
    // }

    // /**
    //  * Sets the value of projectRequests.
    //  *
    //  * @param mixed $projectRequests the project requests
    //  *
    //  * @return self
    //  */
    // public function setProjectRequests($projectRequests)
    // {
    //     $this->projectRequests = $projectRequests;

    //     return $this;
    // }

    // /**
    //  * Gets the value of requests.
    //  *
    //  * @return mixed
    //  */
    // public function getRequests()
    // {
    //     return $this->requests;
    // }

    // /**
    //  * Sets the value of requests.
    //  *
    //  * @param mixed $requests the requests
    //  *
    //  * @return self
    //  */
    // public function setRequests($requests)
    // {
    //     $this->requests = $requests;

    //     return $this;
    // }
}

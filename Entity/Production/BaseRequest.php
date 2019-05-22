<?php

namespace Carbon\ApiBundle\Entity\Production;

use JMS\Serializer\Annotation AS JMS;
use Carbon\ApiBundle\Annotation AS Carbon;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use Carbon\ApiBundle\Entity\BaseCryoblockEntity;

/** @ORM\MappedSuperclass */
abstract class BaseRequest extends BaseCryoblockEntity Implements BaseRequestInterface
{

    /*
        Important note -- All requests must have an object called requestProjects which is a one to many using a linker table which is specific to that implmentation of this abstract class --


         * JMS\VirtualProperty()
         * JMS\Groups({"default"})
        public function getProjectString()
        {
            $projectNames = [];
            if ($this->projectRequests && (is_array($this->projectRequests) || is_object($this->projectRequests))) {
                foreach ($this->projectRequests as $requestProject) {
                    $projectNames[] = $requestProject->getProject()->getName();
                }
                return implode(", ", $projectNames);
            }
        }


    */

    /*
    * @ORM\OneToMany(targetEntity="AppBundle\Entity\Project\ProjectRequest", mappedBy="request")
    */
    abstract public function getRequestProjects();
    abstract public function setRequestProjects($requestProjects);
    abstract public function getProjectString();
    abstract public function getInputSamples();
    abstract public function setInputSamples($inputSamples);
    abstract public function getOutputSamples();
    abstract public function setOutputSamples($outputSamples);

    /*
    transient
    */
    public $projects;
    public $samples;

    const STATUS_PENDING = 'Pending';
    const STATUS_PENDING_PIPELINE = 'Pending-Pipeline';
    const STATUS_PROCESSING = 'Processing';
    const STATUS_ABORTED = 'Aborted';
    const STATUS_COMPLETED = 'Completed';
    const STATUS_ACTION_REQUIRED = 'Action Required';

    /**
     * Valid statuses
     *
     * @var array
     */
    protected $validStatuses = array(
        self::STATUS_PENDING,
        self::STATUS_PENDING_PIPELINE,
        self::STATUS_PROCESSING,
        self::STATUS_ABORTED,
        self::STATUS_COMPLETED,
        self::STATUS_ACTION_REQUIRED,
    );

    /**
     * @var integer $alias
     *
     * @ORM\Column(name="alias", type="string", length=300, nullable=true)
     * @Gedmo\Versioned
     * @JMS\Groups({"default"})
     * @Carbon\Searchable(name="alias")
     */
    protected $alias;

    /**
     * @var string $status
     *
     * @ORM\Column(name="status", type="string", nullable=false)
     * @JMS\Groups({"default"})
     * @Gedmo\Versioned
     */
    protected $status;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=300)
     * @Gedmo\Versioned
     * @JMS\Groups({"default"})
     * @Carbon\Searchable(name="name")
     * @Assert\NotBlank()
     */
    protected $name;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text")
     * @Gedmo\Versioned
     * @JMS\Groups({"default"})
     * @Carbon\Searchable(name="description")
     * @Assert\NotBlank()
     */
    protected $description;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Production\Pipeline")
     * @ORM\JoinColumn(name="pipeline_id", referencedColumnName="id", nullable=true)
     * @JMS\Groups({"default"})
     */
    protected $pipeline;

    /**
     * @ORM\Column(name="pipeline_step", type="integer", nullable=true)
     * @JMS\Groups({"default"})
     */
    protected $pipelineStep;

    // Transient;
    public $inSamples;
    public $outSamples;

    /**
     * Gets the value of alias.
     *
     * @return integer $alias
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * Sets the value of alias.
     *
     * @param integer $alias $alias the alias
     *
     * @return self
     */
    public function setAlias($alias)
    {
        $this->alias = $alias;

        return $this;
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
     * Gets the value of pipeline.
     *
     * @return mixed
     */
    public function getPipeline()
    {
        return $this->pipeline;
    }

    /**
     * Sets the value of pipeline.
     *
     * @param mixed $pipeline the pipeline
     *
     * @return self
     */
    public function setPipeline($pipeline)
    {
        $this->pipeline = $pipeline;

        return $this;
    }

    /**
     * Gets the value of pipelineStep.
     *
     * @return mixed
     */
    public function getPipelineStep()
    {
        return $this->pipelineStep;
    }

    /**
     * Sets the value of pipelineStep.
     *
     * @param mixed $pipelineStep the pipeline step
     *
     * @return self
     */
    public function setPipelineStep($pipelineStep)
    {
        $this->pipelineStep = $pipelineStep;

        return $this;
    }

    /**
     * Gets the value of status.
     *
     * @return string $status
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Sets the value of status.
     *
     * @param string $status $status the status
     *
     * @return self
     */
    public function setStatus($status)
    {
        if (!in_array($status, $this->validStatuses)) {
            throw new \InvalidArgumentException(sprintf('%s is not a valid status', $status));
        }

        $this->status = $status;

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
     * Gets the value of projects.
     *
     * @return mixed
     */
    public function getProjects()
    {
        return $this->projects;
    }

    /**
     * Sets the value of projects.
     *
     * @param mixed $projects the projects
     *
     * @return self
     */
    public function setProjects($projects)
    {
        $this->projects = $projects;

        return $this;
    }

    /**
     * @return array
     */
    public function getValidStatuses()
    {
        return $this->validStatuses;
    }

    /**
     * @return mixed
     */
    public function getInSample()
    {
        return $this->inSample;
    }

    /**
     * @param mixed $inSample
     *
     * @return self
     */
    public function setInSample($inSample)
    {
        $this->inSample = $inSample;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getOutSample()
    {
        return $this->outSample;
    }

    /**
     * @param mixed $outSample
     *
     * @return self
     */
    public function setOutSample($outSample)
    {
        $this->outSample = $outSample;

        return $this;
    }
}

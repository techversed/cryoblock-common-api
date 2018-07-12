<?php

namespace Carbon\ApiBundle\Entity\Production;

use JMS\Serializer\Annotation AS JMS;
use Carbon\ApiBundle\Annotation AS Carbon;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/** @ORM\MappedSuperclass */
abstract class BaseRequest Implements BaseRequestInterface
{
    const STATUS_PENDING = 'Pending';

    const STATUS_PENDING_PIPELINE = 'Pending-Pipeline';

    const STATUS_PROCESSING = 'Processing';

    const STATUS_ABORTED = 'Aborted';

    const STATUS_COMPLETED = 'Completed';

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

    /**
     * @var User $createdBy
     *
     * @Gedmo\Blameable(on="create")
     * @ORM\ManyToOne(targetEntity="Carbon\ApiBundle\Entity\User")
     * @ORM\JoinColumn(name="created_by_id", referencedColumnName="id")
     * @JMS\Groups({"default"})
     */
    protected $createdBy;

    /**
     * Created by id
     * @ORM\Column(name="created_by_id", type="integer", nullable=false)
     * @JMS\Groups({"default"})
     */
    protected $createdById;

    /**
     * @var User $updatedBy
     *
     * @Gedmo\Blameable(on="update")
     * @ORM\ManyToOne(targetEntity="Carbon\ApiBundle\Entity\User")
     * @ORM\JoinColumn(name="updated_by_id", referencedColumnName="id")
     * @JMS\Groups({"default"})
     */
    protected $updatedBy;

    /**
     * Created by id
     * @ORM\Column(name="updated_by_id", type="integer", nullable=false)
     * @JMS\Groups({"default"})
     */
    protected $updatedById;

    /**
     * @var \DateTime $created
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     * @JMS\Groups({"default"})
     */
    protected $createdAt;

    /**
     * @var \DateTime $updated
     *
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime")
     * @JMS\Groups({"default"})
     */
    protected $updatedAt;

    /**
     * @var \DateTime $deletedAt
     *
     * @ORM\Column(name="deleted_at", type="datetime", nullable=true)
     * @JMS\Groups({"default"})
     */
    protected $deletedAt;

    /**
    * @ORM\OneToMany(targetEntity="AppBundle\Entity\Project\ProjectRequest", mappedBy="request")
    */
    protected $projectRequests;


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
     * Gets the value of createdBy.
     *
     * @return User $createdBy
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * Sets the value of createdBy.
     *
     * @param User $createdBy $createdBy the created by
     *
     * @return self
     */
    public function setCreatedBy($createdBy)
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * Gets the Created by id.
     *
     * @return mixed
     */
    public function getCreatedById()
    {
        return $this->createdById;
    }

    /**
     * Sets the Created by id.
     *
     * @param mixed $createdById the created by id
     *
     * @return self
     */
    public function setCreatedById($createdById)
    {
        $this->createdById = $createdById;

        return $this;
    }

    /**
     * Gets the value of updatedBy.
     *
     * @return User $updatedBy
     */
    public function getUpdatedBy()
    {
        return $this->updatedBy;
    }

    /**
     * Sets the value of updatedBy.
     *
     * @param User $updatedBy $updatedBy the updated by
     *
     * @return self
     */
    public function setUpdatedBy($updatedBy)
    {
        $this->updatedBy = $updatedBy;

        return $this;
    }

    /**
     * Gets the Created by id.
     *
     * @return mixed
     */
    public function getUpdatedById()
    {
        return $this->updatedById;
    }

    /**
     * Sets the Created by id.
     *
     * @param mixed $updatedById the updated by id
     *
     * @return self
     */
    public function setUpdatedById($updatedById)
    {
        $this->updatedById = $updatedById;

        return $this;
    }

    /**
     * Gets the value of createdAt.
     *
     * @return \DateTime $created
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Sets the value of createdAt.
     *
     * @param \DateTime $created $createdAt the created at
     *
     * @return self
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

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
     * @return \DateTime $deletedAt
     */
    public function getDeletedAt()
    {
        return $this->deletedAt;
    }

    /**
     * Sets the value of deletedAt.
     *
     * @param \DateTime $deletedAt $deletedAt the deleted at
     *
     * @return self
     */
    public function setDeletedAt($deletedAt)
    {
        $this->deletedAt = $deletedAt;

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
     * @JMS\VirtualProperty()
     * @JMS\Groups({"default"})
     */
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
    /**
     * Gets the value of projectRequests.
     *
     * @return mixed
     */
    public function getProjectRequests()
    {
        return $this->projectRequests;
    }
    /**
     * Sets the value of projectRequests.
     *
     * @param mixed $projectRequests the project requests
     *
     * @return self
     */
    public function setProjectRequests($projectRequests)
    {
        $this->projectRequests = $projectRequests;
        return $this;
    }
}

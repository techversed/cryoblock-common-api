<?php
namespace Carbon\ApiBundle\Entity\Project;

use Carbon\ApiBundle\Annotation As Carbon;
use Carbon\ApiBundle\Entity\BaseCryoblockEntity;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation AS JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\MappedSuperclass
 */
abstract class BaseProjectSequence extends BaseCryoblockEntity
{
    /**
    * @var integer
    * @ORM\Column(name="project_id", type="integer")
    * @JMS\Groups({"default"})
    *
    */
    protected $projectId;

    /**
    * @var integer
    * @ORM\Column(name="sequence_id", type="integer")
    * @JMS\Groups({"default"})
    *
    */
    protected $sequenceId;

    /**
     * @var Project $project
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Project\Project")
     * @ORM\JoinColumn(name="project_id", referencedColumnName="id")
     * @JMS\Groups({"default"})
     */
    protected $project;

    /**
     * @var Sequence $sequence
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Storage\Sequence")
     * @ORM\JoinColumn(name="sequence_id", referencedColumnName="id")
     * @JMS\Groups({"default"})
     */
    protected $sequence;

    /**
     * Gets the value of projectId.
     *
     * @return integer
     */
    public function getProjectId()
    {
        return $this->projectId;
    }

    /**
     * Sets the value of projectId.
     *
     * @param integer $projectId the project id
     *
     * @return self
     */
    public function setProjectId($projectId)
    {
        $this->projectId = $projectId;

        return $this;
    }

    /**
     * Gets the value of sequenceId.
     *
     * @return integer
     */
    public function getSequenceId()
    {
        return $this->sequenceId;
    }

    /**
     * Sets the value of sequenceId.
     *
     * @param integer $sequenceId the sequence id
     *
     * @return self
     */
    public function setSequenceId($sequenceId)
    {
        $this->sequenceId = $sequenceId;

        return $this;
    }

    /**
     * Gets the value of project.
     *
     * @return Project $project
     */
    public function getProject()
    {
        return $this->project;
    }

    /**
     * Sets the value of project.
     *
     * @param Project $project $project the project
     *
     * @return self
     */
    public function setProject($project)
    {
        $this->project = $project;

        return $this;
    }

    /**
     * Gets the value of sequence.
     *
     * @return Sequence $sequence
     */
    public function getSequence()
    {
        return $this->sequence;
    }

    /**
     * Sets the value of sequence.
     *
     * @param Sequence $sequence $sequence the sequence
     *
     * @return self
     */
    public function setSequence($sequence)
    {
        $this->sequence = $sequence;

        return $this;
    }
}

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
class BaseProjectRequest extends BaseCryoblockEntity
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
    * @ORM\Column(name="request_id", type="integer")
    * @JMS\Groups({"default"})
    *
    */
    protected $requestId;
    /**
     * @var Project $project
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Project\Project")
     * @ORM\JoinColumn(name="project_id", referencedColumnName="id")
     * @JMS\Groups({"default"})
     */
    protected $project;
    /**
     * @var Request $request
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Production\Request")
     * @ORM\JoinColumn(name="request_id", referencedColumnName="id")
     * @JMS\Groups({"default"})
     */
    protected $request;
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
     * Gets the value of requestId.
     *
     * @return integer
     */
    public function getRequestId()
    {
        return $this->requestId;
    }
    /**
     * Sets the value of requestId.
     *
     * @param integer $requestId the request id
     *
     * @return self
     */
    public function setRequestId($requestId)
    {
        $this->requestId = $requestId;
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
     * Gets the value of request.
     *
     * @return Request $request
     */
    public function getRequest()
    {
        return $this->request;
    }
    /**
     * Sets the value of request.
     *
     * @param Request $request $request the request
     *
     * @return self
     */
    public function setRequest($request)
    {
        $this->request = $request;
        return $this;
    }
}

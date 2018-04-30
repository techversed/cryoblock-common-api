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
class BaseProjectSample extends BaseCryoblockEntity
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
    * @JMS\Groups({"default"})
    *
    */
    protected $sampleId;

    /**
     * @var Project $project
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Project\Project")
     * @ORM\JoinColumn(name="project_id", referencedColumnName="id")
     * @JMS\Groups({"default"})
     */
    protected $project;

    /**
     * @var Sample $sample
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Storage\Sample")
     * @ORM\JoinColumn(name="sample_id", referencedColumnName="id")
     * @JMS\Groups({"default"})
     */
    protected $sample;


}

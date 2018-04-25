<?php
namespace Carbon\ApiBundle\Entity\Storage

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

    /*
    * @var integer
    * @ORM\Column(name="project_id", type="integer")
    *
    *
    */
    protected $projectId;

    /*
    *
    *
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


}

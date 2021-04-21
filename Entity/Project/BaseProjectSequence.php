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

    abstract public function getProjectId();
    abstract public function setProjectId($projectId);

    abstract public function getProject();
    abstract public function setProject($project);

    abstract public function getSequence();
    abstract public function setSequence($sequence);

    abstract public function getSequenceId();
    abstract public function setSequenceId($sequenceId);

}

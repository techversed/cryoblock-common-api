<?php
namespace Carbon\ApiBundle\Controller\Project;

use Carbon\ApiBundle\Controller\CarbonApiController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFounndation\Response;

class BaseProjectSequenceController extends CarbonApiController
{

    /**
     * @var string The namespace of the resource entity
     */
    const RESOURCE_ENTITY = "AppBundle\Entity\Project\ProjectAntibodySequence";

    protected $resourceLinkMap = array(
        'project' => array(
            'returnedEntity' => 'AppBundle\Entity\Storage\Sequence',
            'joinColumn' => 'sequenceId',
            'whereColumn' => 'projectId',
        ),
        'sequence' => array(
            'returnedEntity' => 'AppBundle\Entity\Project\Project',
            'joinColumn' => 'projectId',
            'whereColumn' => 'sequenceId',
        )
    );

    /**
     * @Route("/project/sequence/{type}/{id}", name="project_sequence_id_get")
     * @Method("GET")
     *
     * @return Response
     */
    public function getAction($type, $id)
    {
        return $this->handleMTMGet($type, $id);
    }
}

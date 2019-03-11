<?php
namespace Carbon\ApiBundle\Controller\Project;

use Carbon\ApiBundle\Controller\CarbonApiController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFounndation\Response;

abstract class BaseProjectSampleController extends CarbonApiController
{

    /**
     * @var string The namespace of the resource entity
     */
    const RESOURCE_ENTITY = "AppBundle\Entity\Project\ProjectSample";

    protected $resourceLinkMap = array(
        'project' => array(
            'returnedEntity' => 'AppBundle\Entity\Storage\Sample',
            'joinColumn' => 'sampleId',
            'whereColumn' => 'projectId',
        ),
        'sample' => array(
            'returnedEntity' => 'AppBundle\Entity\Project\Project',
            'joinColumn' => 'projectId',
            'whereColumn' => 'sampleId',
        )
    );

    /**
     * @Route("/project/sample/{type}/{id}", name="project_sample_id_get")
     * @Method("GET")
     *
     * @return Response
     */
    public function getAction($type, $id)
    {
        return $this->handleMTMGet($type, $id);
    }
}

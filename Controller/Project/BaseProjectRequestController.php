<?php
namespace Carbon\ApiBundle\Controller\Project;

use Carbon\ApiBundle\Controller\CarbonApiController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFounndation\Response;

class BaseProjectRequestController extends CarbonApiController
{

    /**
     * @var string The namespace of the resource entity
     */
    const RESOURCE_ENTITY = "AppBundle\Entity\Project\ProjectRequest";

    protected $resourceLinkMap = array(
        'project' => array(
            'returnedEntity' => 'AppBundle\Entity\Production\Request',
            'joinColumn' => 'requestId',
            'whereColumn' => 'projectId',
        ),
        'request' => array(
            'returnedEntity' => 'AppBundle\Entity\Project\Project',
            'joinColumn' => 'projectId',
            'whereColumn' => 'requestId',
        )
    );

    /**
     * @Route("/project/request/{type}/{id}", name="project_request_id_get")
     * @Method("GET")
     *
     * @return Response
     */
    public function getAction($type, $id)
    {
        return $this->handleMTMGet($type, $id);
    }
}

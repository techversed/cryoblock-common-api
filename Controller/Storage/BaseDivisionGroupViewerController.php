<?php

namespace Carbon\ApiBundle\Controller\Storage;

use Carbon\ApiBundle\Controller\CarbonApiController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Response;

class BaseDivisionGroupViewerController extends CarbonApiController
{
    /**
     * @var string The namespace of the resource entity
     */
    const RESOURCE_ENTITY = "AppBundle\Entity\Storage\DivisionGroupViewer";

    protected $resourceLinkMap = array(
        'division' => array(
            'returnedEntity' => 'Carbon\ApiBundle\Entity\Group',
            'joinColumn' => 'groupId',
            'whereColumn' => 'divisionId',
        ),
        'group' => array(
            'returnedEntity' => 'AppBundle\Entity\Storage\Division',
            'joinColumn' => 'divisionId',
            'whereColumn' => 'groupId',
        )
    );

    /**
     * @Route("/storage/division-group-viewer/{type}/{id}", name="division_group_viewer_get")
     * @Method("GET")
     *
     * @return Response
     */
    public function getAction($type, $id)
    {
        return $this->handleMTMGet($type, $id);
    }
}

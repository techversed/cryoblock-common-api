<?php

namespace Carbon\ApiBundle\Controller\Help;

use Carbon\ApiBundle\Controller\CarbonApiController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Response;

class BaseHelpGroupViewerController extends CarbonApiController
{
    /**
     * @var string The namespace of the resource entity
     */
    const RESOURCE_ENTITY = "AppBundle\Entity\Help\HelpGroupViewer";

    protected $resourceLinkMap = array(
        'help' => array(
            'returnedEntity' => 'Carbon\ApiBundle\Entity\Group',
            'joinColumn' => 'groupId',
            'whereColumn' => 'helpId',
        ),
        'group' => array(
            'returnedEntity' => 'AppBundle\Entity\Help\Help',
            'joinColumn' => 'helpId',
            'whereColumn' => 'groupId',
        )
    );

    /**
     * @Route("/help/help-group-viewer/{type}/{id}", name="help_group_viewer_get")
     * @Method("GET")
     *
     * @return Response
     */
    public function getAction($type, $id)
    {
        return $this->handleMTMGet($type, $id);
    }
}

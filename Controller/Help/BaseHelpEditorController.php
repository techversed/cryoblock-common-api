<?php

namespace Carbon\ApiBundle\Controller\Help;

use Carbon\ApiBundle\Controller\CarbonApiController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Response;

class BaseHelpEditorController extends CarbonApiController
{
    /**
     * @var string The namespace of the resource entity
     */
    const RESOURCE_ENTITY = "AppBundle\Entity\Help\HelpEditor";

    protected $resourceLinkMap = array(
        'help' => array(
            'returnedEntity' => 'Carbon\ApiBundle\Entity\User',
            'joinColumn' => 'userId',
            'whereColumn' => 'helpId',
        ),
        'user' => array(
            'returnedEntity' => 'AppBundle\Entity\Help\Help',
            'joinColumn' => 'helpId',
            'whereColumn' => 'userId',
        )
    );

    /**
     * @Route("/help/help-editor/{type}/{id}", name="help_editor_get")
     * @Method("GET")
     *
     * @return Response
     */
    public function getAction($type, $id)
    {
        return $this->handleMTMGet($type, $id);
    }
}

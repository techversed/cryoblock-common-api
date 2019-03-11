<?php

namespace Carbon\ApiBundle\Controller\Storage;

use Carbon\ApiBundle\Controller\CarbonApiController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Response;

abstract class BaseDivisionEditorController extends CarbonApiController
{
    /**
     * @var string The namespace of the resource entity
     */
    const RESOURCE_ENTITY = "AppBundle\Entity\Storage\DivisionEditor";

    protected $resourceLinkMap = array(
        'division' => array(
            'returnedEntity' => 'Carbon\ApiBundle\Entity\User',
            'joinColumn' => 'userId',
            'whereColumn' => 'divisionId',
        ),
        'user' => array(
            'returnedEntity' => 'AppBundle\Entity\Storage\Division',
            'joinColumn' => 'divisionId',
            'whereColumn' => 'userId',
        )
    );

    /**
     * @Route("/storage/division-editor/{type}/{id}", name="division_editor_get")
     * @Method("GET")
     *
     * @return Response
     */
    public function getAction($type, $id)
    {
        return $this->handleMTMGet($type, $id);
    }
}

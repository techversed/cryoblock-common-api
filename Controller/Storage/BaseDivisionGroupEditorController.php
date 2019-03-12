<?php

namespace Carbon\ApiBundle\Controller\Storage;

use Carbon\ApiBundle\Controller\CarbonApiController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Response;

class BaseDivisionGroupEditorController extends CarbonApiController
{
    /**
     * @var string The namespace of the resource entity
     */
    const RESOURCE_ENTITY = "AppBundle\Entity\Storage\DivisionGroupEditor";

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
     * @Route("/storage/division-group-editor/{type}/{id}", name="division_group_editor_get")
     * @Method("GET")
     *
     * @return Response
     */
    public function getAction($type, $id)
    {
        return $this->handleMTMGet($type, $id);
    }
}

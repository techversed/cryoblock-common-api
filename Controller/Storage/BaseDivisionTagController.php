<?php

namespace Carbon\ApiBundle\Controller\Storage;

use Carbon\ApiBundle\Controller\CarbonApiController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Response;

class BaseDivisionTagController extends CarbonApiController
{
    /**
     * @var string The namespace of the resource entity
     */
    const RESOURCE_ENTITY = "AppBundle\Entity\Storage\DivisionTag";

    protected $resourceLinkMap = array(
        'division' => array(
            'returnedEntity' => 'AppBundle\Entity\Storage\Tag',
            'joinColumn' => 'tagId',
            'whereColumn' => 'divisionId',
        ),
        'sample-type' => array(
            'returnedEntity' => 'AppBundle\Entity\Storage\Division',
            'joinColumn' => 'divisionId',
            'whereColumn' => 'tagId',
        )
    );

    /**
     * @Route("/storage/division-tag/{type}/{id}", name="division_tag_get")
     * @Method("GET")
     *
     * @return Response
     */
    public function getAction($type, $id)
    {
        return parent::handleMTMGet($type, $id);
    }
}

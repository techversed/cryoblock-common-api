<?php

namespace Carbon\ApiBundle\Controller\Storage;

use Carbon\ApiBundle\Controller\CarbonApiController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Response;

abtract class BaseDivisionSampleTypeController extends CarbonApiController
{
    /**
     * @var string The namespace of the resource entity
     */
    const RESOURCE_ENTITY = "AppBundle\Entity\Storage\DivisionSampleType";

    protected $resourceLinkMap = array(
        'division' => array(
            'returnedEntity' => 'AppBundle\Entity\Storage\SampleType',
            'joinColumn' => 'sampleTypeId',
            'whereColumn' => 'divisionId',
        ),
        'sample-type' => array(
            'returnedEntity' => 'AppBundle\Entity\Storage\Division',
            'joinColumn' => 'divisionId',
            'whereColumn' => 'sampleTypeId',
        )
    );

    /**
     * @Route("/storage/division-sample-type/{type}/{id}", name="division_sample_type_get")
     * @Method("GET")
     *
     * @return Response
     */
    public function getAction($type, $id)
    {
        return parent::handleMTMGet($type, $id);
    }
}

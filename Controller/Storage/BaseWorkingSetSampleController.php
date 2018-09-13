<?php

namespace Carbon\ApiBundle\Controller\Storage;

use Carbon\ApiBundle\Controller\CarbonApiController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Response;

class BaseWorkingSetSampleController extends CarbonApiController
{
    /**
     * @var string The namespace of the resource entity
     */
    const RESOURCE_ENTITY = "AppBundle\Entity\Storage\WorkingSetSample";

    protected $resourceLinkMap = array(
        'sample' => array(
            'returnedEntity' => 'AppBundle\Entity\Storage\WorkingSet',
            'joinColumn' => 'workingSetId',
            'whereColumn' => 'sampleId',
        ),
        'workingSet' => array(
            'returnedEntity' => 'AppBundle\Entity\Storage\Sample',
            'joinColumn' => 'sampleId',
            'whereColumn' => 'workingSetId',
        )
    );

    /**
     * @Route("/storage/working-set-sample/{type}/{id}", name="working_set_sample_get")
     * @Method("GET")
     *
     * @return Response
     */
    public function getAction($type, $id)
    {
        return parent::handleMTMGet($type, $id);
    }

}

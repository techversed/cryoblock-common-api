<?php

namespace Carbon\ApiBundle\Controller\Storage;

use Carbon\ApiBundle\Controller\CarbonApiController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Response;

class BaseSampleTagController extends CarbonApiController
{
    /**
     * @var string The namespace of the resource entity
     */
    const RESOURCE_ENTITY = "AppBundle\Entity\Storage\SampleTag";

    protected $resourceLinkMap = array(
        'sample' => array(
            'returnedEntity' => 'AppBundle\Entity\Storage\Tag',
            'joinColumn' => 'tagId',
            'whereColumn' => 'sampleId',
        ),
        'sample-type' => array(
            'returnedEntity' => 'AppBundle\Entity\Storage\Sample',
            'joinColumn' => 'sampleId',
            'whereColumn' => 'tagId',
        )
    );

    /**
     * @Route("/storage/sample-tag/{type}/{id}", name="sample_tag_get")
     * @Method("GET")
     *
     * @return Response
     */
    public function getAction($type, $id)
    {
        return parent::handleMTMGet($type, $id);
    }
}

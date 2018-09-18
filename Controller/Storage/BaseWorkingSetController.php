<?php

namespace Carbon\ApiBundle\Controller\Storage;

use AppBundle\Entity\Storage\Sample;

use Carbon\ApiBundle\Controller\CarbonApiController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class BaseWorkingSetController extends CarbonApiController
{
    /**
     * @var string The namespace of the resource entity
     */
    const RESOURCE_ENTITY = "AppBundle\Entity\Storage\WorkingSet";

    /**
     * Security config
     */
    protected $security = array(
        'GET' => array(
            'roles' => array('ROLE_USER'),
        ),
        'POST' => array(
            'roles' => array('ROLE_USER'),
        ),
        'PUT' => array(
            'roles' => array('ROLE_USER'),
        ),
        'DELETE' => array(
            'roles' => array('ROLE_USER'),
        )
    );

    protected $resourceLinkMap = array(
        'sample' => array(
            'returnedEntity' => 'Carbon\ApiBundle\Entity\User',
            'joinColumn' => 'workingSetId',
            'whereColumn' => 'sampleId',
        ),
        'user' => array(
            'returnedEntity' => 'AppBundle\Entity\Storage\Sample',
            'joinColumn' => 'sampleId',
            'whereColumn' => 'createdById',
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

    // Add a post option here
    // Add a delete option here
    // Also need to add a form type.

}



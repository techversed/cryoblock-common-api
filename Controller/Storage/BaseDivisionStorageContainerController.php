<?php

namespace Carbon\ApiBundle\Controller\Storage;

use Carbon\ApiBundle\Controller\CarbonApiController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Response;

// VIOLATION -- We should not have any routes set in common -- if anything there should be a constant variable set in crowelab and we should use that constant in the annoation for the route if possible ...

class BaseDivisionStorageContainerController extends CarbonApiController
{
    /**
     * @var string The namespace of the resource entity
     */
    const RESOURCE_ENTITY = "AppBundle\Entity\Storage\DivisionStorageContainer";

    protected $resourceLinkMap = array(
        'division' => array(
            'returnedEntity' => 'AppBundle\Entity\Storage\StorageContainer',
            'joinColumn' => 'storageContainerId',
            'whereColumn' => 'divisionId',
        ),
        'storage-container' => array(
            'returnedEntity' => 'AppBundle\Entity\Storage\Division',
            'joinColumn' => 'divisionId',
            'whereColumn' => 'storageContainerId',
        )
    );

    /**
     * @Route("/storage/division-storage-container/{type}/{id}", name="division_storage_container_get")
     * @Method("GET")
     *
     * @return Response
     */
    public function getAction($type, $id)
    {
        return parent::handleMTMGet($type, $id);
    }
}

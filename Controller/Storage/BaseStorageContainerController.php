<?php

namespace Carbon\ApiBundle\Controller\Storage;

use AppBundle\Entity\StorageContainer;
use Carbon\ApiBundle\Controller\CarbonApiController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Response;

class BaseStorageContainerController extends CarbonApiController
{
    /**
     * @var string The namespace of the resource entity
     */
    const RESOURCE_ENTITY = "AppBundle\Entity\Storage\StorageContainer";

    /**
     * Handles the HTTP get request for the division entity
     *
     * @Route("/storage/storage-container", name="storage_container_get")
     * @Method("GET")
     * @return [type] [description]
     */
    public function handleGet()
    {
        return parent::handleGet();
    }

    /**
     * Handles the HTTP get request for the card entity
     *
     * @Route("/storage/storage-container", name="storage_container_post")
     * @Method("POST")
     * @return Response
     */
    public function handlePost()
    {
        return parent::handlePost();
    }

    /**
     * Handles the HTTP PUT request for the card entity
     *
     * @todo  figure out why PUT method has no request params
     * @Route("/storage/storage-container", name="storage_container_put")
     * @Method("PUT")
     * @return [type] [description]
     */
    public function handlePut()
    {
        return parent::handlePut();
    }

    /**
     * Handles the HTTP DELETE request for the card entity
     *
     * @Route("/storage/storage-container", name="storage_container_delete")
     * @Method("DELETE")
     * @return [type] [description]
     */
    public function handleDelete()
    {
        return parent::handleDelete();
    }
}

<?php

namespace Carbon\ApiBundle\Controller\Storage;

use AppBundle\Entity\SampleType;
use Carbon\ApiBundle\Controller\CarbonApiController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Response;

class BaseSampleTypeController extends CarbonApiController
{
    /**
     * @var string The namespace of the resource entity
     */
    const RESOURCE_ENTITY = "AppBundle\Entity\Storage\SampleType";

    /**
     * Handles the HTTP get request for the division entity
     *
     * @Route("/storage/sample-type", name="sample_type_get")
     * @Method("GET")
     * @return Response
     */
    public function handleGet()
    {
        return parent::handleGet();
    }

    /**
     * Handles the HTTP get request for the card entity
     *
     * @Route("/storage/sample-type", name="sample_type_post")
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
     * @Route("/storage/sample-type", name="sample_type_put")
     * @Method("PUT")
     * @return Response
     */
    public function handlePut()
    {
        return parent::handlePut();
    }

    /**
     * Handles the HTTP DELETE request for the card entity
     *
     * @Route("/storage/sample-type", name="sample_type_delete")
     * @Method("DELETE")
     * @return Response
     */
    public function handleDelete()
    {
        return parent::handleDelete();
    }

    /**
     * @Route("/storage/sample-type", name="sample_type_patch")
     * @Method("PATCH")
     *
     * @return Response
     */
    public function handlePatch()
    {
        return parent::handlePatch();
    }

    /**
     * @Route("/storage/sample-type", name="sample_type_purge")
     * @Method("PURGE")
     *
     * @return Response
     */
    public function handlePurge()
    {
        return parent::handlePurge();
    }
}

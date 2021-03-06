<?php

namespace Carbon\ApiBundle\Controller;

use Carbon\ApiBundle\Controller\CarbonApiController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class GroupController extends CarbonApiController
{
    /**
     * @var string The namespace of the resource entity
     */
    const RESOURCE_ENTITY = "Carbon\ApiBundle\Entity\Group";

    /**
     * @var string The form type for this resource
     */
    const FORM_TYPE = "group";

    /**
     * @Route("/group", name="group_get")
     * @Method("GET")
     * @Security("has_role('ROLE_USER')")
     *
     * @return Response
     */
    public function getAction()
    {
        return parent::handleGet();
    }

    /**
     * Handles the HTTP POST request for the group entity
     *
     * @Route("/group", name="group_post")
     * @Method("POST")
     * @Security("has_role('ROLE_ADMIN')")
     *
     * @return Response
     */
    public function handlePost()
    {
        return parent::handlePost();
    }

    /**
     * Handles the HTTP PUT request for the group entity
     *
     * @Route("/group", name="group_put")
     * @Method("PUT")
     * @Security("has_role('ROLE_ADMIN')")
     *
     * @return Response
     */
    public function handlePut()
    {
        return parent::handlePut();
    }

    /**
     * Handles the HTTP DELETE request for the group entity
     *
     * @Route("/group", name="group_delete")
     * @Method("DELETE")
     * @Security("has_role('ROLE_ADMIN')")
     *
     * @return Response
     */
    public function handleDelete()
    {
        return parent::handleDelete();
    }
}

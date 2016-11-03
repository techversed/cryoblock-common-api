<?php

namespace Carbon\ApiBundle\Controller;

use Carbon\ApiBundle\Controller\CarbonApiController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleController extends CarbonApiController
{
    /**
     * @var string The namespace of the resource entity
     */
    const RESOURCE_ENTITY = "Carbon\ApiBundle\Entity\Role";

    /**
     * @Route("/role", name="role_options")
     * @Method("OPTIONS")
     *
     * @return Response
     */
    public function optionsAction()
    {
        $response = new Response();

        $data = array('success' => 'success');

        return $this->getJsonResponse(json_encode($data));
    }

    /**
     * @Route("/role", name="role_get")
     * @Method("GET")
     *
     * @return Response
     */
    public function getAction()
    {
        return parent::handleGet();
    }
}

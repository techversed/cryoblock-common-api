<?php

namespace Carbon\ApiBundle\Controller;

use Carbon\ApiBundle\Controller\CarbonApiController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Response;

class GroupRoleController extends CarbonApiController
{
    /**
     * @var string The namespace of the resource entity
     */
    const RESOURCE_ENTITY = "Carbon\ApiBundle\Entity\GroupRole";

    protected $resourceLinkMap = array(
        'group' => array(
            'returnedEntity' => 'Carbon\ApiBundle\Entity\Role',
            'joinColumn' => 'roleId',
            'whereColumn' => 'groupId',
        ),
        'role' => array(
            'returnedEntity' => 'Carbon\ApiBundle\Entity\Group',
            'joinColumn' => 'groupId',
            'whereColumn' => 'roleId',
        )
    );

    /**
     * @Route("/group-role/{type}/{id}", name="group_role_options")
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
     * @Route("/group-role/{type}/{id}", name="group_role_get")
     * @Method("GET")
     *
     * @return Response
     */
    public function getAction($type, $id)
    {
        return parent::handleMTMGet($type, $id);
    }
}

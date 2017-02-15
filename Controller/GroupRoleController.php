<?php

namespace Carbon\ApiBundle\Controller;

use Carbon\ApiBundle\Controller\CarbonApiController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
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
     * Handles the HTTP GET request for the group role entity
     *
     * @Route("/group-role/{type}/{id}", name="group_role_get")
     * @Method("GET")
     * @Security("has_role('ROLE_USER')")
     *
     * @return Response
     */
    public function getAction($type, $id)
    {
        return $this->handleMTMGet($type, $id);
    }
}

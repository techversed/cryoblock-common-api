<?php

namespace Carbon\ApiBundle\Controller;

use Carbon\ApiBundle\Controller\CarbonApiController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Response;

class UserGroupController extends CarbonApiController
{
    /**
     * @var string The namespace of the resource entity
     */
    const RESOURCE_ENTITY = "Carbon\ApiBundle\Entity\UserGroup";

    protected $resourceLinkMap = array(
        'user' => array(
            'returnedEntity' => 'Carbon\ApiBundle\Entity\Group',
            'joinColumn' => 'groupId',
            'whereColumn' => 'userId',
        ),
        'group' => array(
            'returnedEntity' => 'Carbon\ApiBundle\Entity\User',
            'joinColumn' => 'userId',
            'whereColumn' => 'groupId',
        ),
    );

    /**
     * @Route("/user-group/{type}/{id}", name="user_group_options")
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
     * @Route("/user-group/{type}/{id}", name="user_group_get")
     * @Method("GET")
     *
     * @return Response
     */
    public function getAction($type, $id)
    {
        return parent::handleMTMGet($type, $id);
    }
}

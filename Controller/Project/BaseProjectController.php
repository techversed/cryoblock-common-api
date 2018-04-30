<?php
namespace Carbon\ApiBundle\Controller\Project;

use AppBundle\Entity\Storage\Division;
use Carbon\ApiBundle\Controller\CarbonApiController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class BaseProjectController extends CarbonApiController
{

    /**
    * @var string The namespace of the resource entity
    */
    const RESOURCE_ENTITY = "AppBundle\Entity\Project\Project";

    /**
    * @var string The form type of this resource
    */
    const FORM_TYPE = "project";

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

    /**
    * Handle the HTTP get request for project
    * @Route("/project/project", name="project_get")
    * @Method("GET")
    *
    * @return Response
    */
    public function handleGet()
    {
        return parent::handleGet();
    }

    /**
    * Handle POST requests for Project
    * @Route("/project/project", name="project_post")
    * @Method("POST")
    *
    * @return Response
    */
    public function handlePost()
    {
        return parent::handlePost();
    }

    //FOR LATER
    //put
    //patch
    //purge
    //delete

}

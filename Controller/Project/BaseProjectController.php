<?php
namespace Carbon\ApiBundle\Controller\Project;

use AppBundle\Entity\Storage\Division;
use Carbon\ApiBundle\Controller\CarbonApiController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
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
    * Handle the HTTP get request for division
    * @Route("/project", name="project_get")
    * @Method("GET")
    *
    * @return Response
    */
    public function handleGet()
    {
        return parent::handleGet();
    }

}

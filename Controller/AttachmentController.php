<?php

namespace Carbon\ApiBundle\Controller;

use Carbon\ApiBundle\Controller\CarbonApiController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class AttachmentController extends CarbonApiController
{
    /**
     * @var string The namespace of the resource entity
     */
    const RESOURCE_ENTITY = "Carbon\ApiBundle\Entity\Attachment";

    /**
     * Handles the HTTP get request for the attachment entity
     *
     * @Route("/attachment", name="attachment_get")
     * @Method("GET")
     * @Security("is_granted('ROLE_USER')")
     *
     * @return Response
     */
    public function handleGet()
    {
        return parent::handleGet();
    }

    /**
     * Handles the HTTP DELETE request for the attachment entity
     *
     * @Route("/attachment", name="attachment_delete")
     * @Method("DELETE")
     * @Security("is_granted('ROLE_USER')")
     *
     * @return Response
     */
    public function handleDelete()
    {
        return parent::handleDelete();
    }
}

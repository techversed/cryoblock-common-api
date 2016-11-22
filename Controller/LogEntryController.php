<?php

namespace Carbon\ApiBundle\Controller;

use Carbon\ApiBundle\Controller\CarbonApiController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class LogEntryController extends CarbonApiController
{
    /**
     * @var string The namespace of the resource entity
     */
    const RESOURCE_ENTITY = "Gedmo\Loggable\Entity\LogEntry";

    /**
     * @Route("/log-entry", name="log_entry_options")
     * @Method("OPTIONS")
     *
     * @return Symfony\Component\HttpFoundation\Response
     */
    public function optionsAction()
    {
        $data = array('success' => 'success');

        return $this->getJsonResponse(json_encode($data));
    }

    /**
     * Handles the HTTP GET request for the log entry entity
     *
     * @Route("/log-entry", name="log_entry_get")
     * @Method("GET")
     * @Security("has_role('ROLE_USER')")
     *
     * @return Symfony\Component\HttpFoundation\Response
     */
    public function handleGet()
    {
        return parent::handleGet();
    }
}

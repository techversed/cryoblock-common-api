<?php

namespace Carbon\ApiBundle\Controller;

use Carbon\ApiBundle\Controller\CarbonApiController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

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
     * Handles the HTTP get request for the card entity
     *
     * @Route("/log-entry", name="log_entry_get")
     * @Method("GET")
     *
     * @return Symfony\Component\HttpFoundation\Response
     */
    public function handleGet()
    {
        return parent::handleGet();
    }
}

<?php

namespace Carbon\ApiBundle\Controller;

use Carbon\ApiBundle\Controller\CarbonApiController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/*
    This is in common so it is bad form to have it refer to the role 'ROLE_UNDERGRAD_STUDENT_WORKER' since that is specific to the crowelab implementation --
    If the role does not exist then it will not cause any long term problems but it is still a good idea to figure out a better long term method of hanlding this.

*/

class LogEntryController extends CarbonApiController
{
    /**
     * @var string The namespace of the resource entity
     */
    const RESOURCE_ENTITY = "Gedmo\Loggable\Entity\LogEntry";

    /**
     * Handles the HTTP GET request for the log entry entity
     *
     * @Route("/log-entry", name="log_entry_get")
     * @Method("GET")
     * @Security("has_role('ROLE_USER') || has_role('ROLE_UNDERGRAD_STUDENT_WORKER')")
     *
     * @return Symfony\Component\HttpFoundation\Response
     */
    public function handleGet()
    {
        return parent::handleGet();
    }
}

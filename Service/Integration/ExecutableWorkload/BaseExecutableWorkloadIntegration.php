<?php

namespace Carbon\ApiBundle\Integration;

use Carbon\ApiBundle\Integration\BaseIntegrationInterface;

/*
    This is used for interfaces which


*/

interface BaseExecutableWorkloadIntegration extends BaseIntegrationInterface
{

    // Used for things like PyIR ... etc

    public function registerCallback();

    // Check upon the status of a given workload -- Completed, Pending, Running... etc
    public function getStatus();


}

/*
// Interface extends BaseIntegrationInterface.php



*/

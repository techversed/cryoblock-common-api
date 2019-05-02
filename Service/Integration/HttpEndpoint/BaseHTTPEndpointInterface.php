<?php

namespace Carbon\ApiBundle\Integration;

use Carbon\ApiBundle\Integration\BaseIntegrationInterface;

/*
    This interface should only be implemented by base classes -- Any class which wishes
    to Implement this should have a base version in common.


*/

interface BaseHttpEndpointInterface extends BaseIntegrationInterface
{



}

/*
Interface extends BaseIntegrationInterface

    public function getEndPoint($type);

    public function obtainSession();

    public function checkStatus();

    public function upateDataModel();

    public function postBodyRequest($body, $endpoint, $overrides = array());

    public function getResource();
*/

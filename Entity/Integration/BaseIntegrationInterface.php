<?php

namespace Carbon\ApiBundle\Entity\Integration;

interface BaseIntegrationInterface
{

    public function getStatus();

    public function getStartTime();

    public function getEndTime();

    public function setCallaback(); // provide callback or say if a callback will be needed



    // public function set

}

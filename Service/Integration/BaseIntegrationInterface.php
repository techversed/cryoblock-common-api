<?php

namespace Carbon\ApiBundle\Entity\Integration;

/*
    Classes should not directly implement this interface -- they should implement interfaces which extend this

*/

interface BaseIntegrationInterface
{

    public function getStatus();

    // public function getStartTime();

    // public function getEndTime();

    public function setCallaback(); // provide callback or say if a callback will be needed


    // public function set
}

<?php

namespace Carbon\ApiBundle\Entity\Production;

interface BaseRequestInterface
{
    public function getInputSamples();

    public function setInputSamples($inputSamples);

    public function getOutputSamples();

    public function setOutputSamples($outputSamples);

    public function getAliasPrefix();

    public function getRequestProjects();

    public function setRequestProjects($requestProjects);

    public function getProjectString();

}

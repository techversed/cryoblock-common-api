<?php

namespace Carbon\ApiBundle\GridForm;

interface GridFormInterface
{


    public function setClassname($className);

    public function getClassname();

    public function setServicename($serviceName);

    public function getServicename();

    public function addEntityId($id);

    public function removeEntityId($id);

}

<?php

namespace Carbon\ApiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

abstract class CarbonController extends Controller
{
    public function getEntityRepository($entity)
    {
        return $this->getEntityManager()->getEntityRepository($entity);
    }

    public function getEntityManager()
    {
        return $this->get('doctrine.orm.default_entity_manager');
    }
}

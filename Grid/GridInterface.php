<?php

namespace Carbon\ApiBundle\Grid;

use Doctrine\ORM\EntityRepository;

interface GridInterface
{
    public function getResult(EntityRepository $repo);
}

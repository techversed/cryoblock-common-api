<?php

namespace Carbon\ApiBundle\Entity;

use AppBundle\Entity\SampleType;
use AppBundle\Entity\StorageContainer;
use Doctrine\ORM\EntityRepository;
use Gedmo\Tree\Entity\Repository\MaterializedPathRepository;

/**
 * Comment Repository
 */
class CommentRepository extends MaterializedPathRepository
{
}

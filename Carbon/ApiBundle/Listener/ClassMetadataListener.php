<?php

namespace Carbon\ApiBundle\Listener;

use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Monolog\Logger;

class ClassMetadataListener
{
    protected $logger;

    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs)
    {
        $classMetadata = $eventArgs->getClassMetadata();

        if ($classMetadata->reflClass->name == 'FOS\UserBundle\Model\User') {

            unset($classMetadata->fieldMappings['roles']);

        }

        if ($classMetadata->reflClass->name == 'FOS\UserBundle\Model\Group') {

            unset($classMetadata->fieldMappings['roles']);

        }
    }
}

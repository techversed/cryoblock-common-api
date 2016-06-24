<?php

namespace Carbon\ApiBundle\Serializer;

use JMS\Serializer\Metadata\PropertyMetadata;
use JMS\Serializer\Naming\PropertyNamingStrategyInterface;

class CarbonNamingStrategy implements PropertyNamingStrategyInterface
{
    public function translateName(PropertyMetadata $metadata)
    {
        return $metadata->name;
    }
}

<?php

namespace Carbon\ApiBundle\Service;

use JMS\Serializer\Naming\IdenticalPropertyNamingStrategy;
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializerBuilder;
use JMS\Serializer\SerializationContext;
use Symfony\Component\HttpKernel\Config\FileLocator;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Builds the SerializationContext from GET Parameters
 */
class SerializationHelper
{
    /**
     * HEADER name for specific serialization groups used by JMSSerializer
     *
     * @var string
     */
    const HEADER_SERIALIZATION_GROUPS = 'X-CARBON-SERIALIZATION-GROUPS';

    /**
     * @var JMS\Serializer\Serializer
     */
    protected $serializer;

    /**
     * @var Symfony\Component\HttpFoundation\Request
     */
    protected $request;

    /**
     * Initializes a new SerializationHelper instance
     *
     * @param RequestStack $requestStack
     */
    public function __construct(Serializer $serializer, RequestStack $requestStack)
    {
        $this->serializer = $serializer;
        $this->request = $requestStack->getCurrentRequest();
    }

    public function serialize($data, $groups = array(), $type = 'json')
    {
        return $this->serializer->serialize($data, $type, $this->buildSerializationContext($groups));
    }

    public function serializeWithContext($data, $context, $type= 'json') {
        return $this->serializer->serialize($data, $type, $context);
    }

    public function getSerializer()
    {
        return $this->serializer;
    }

    /**
     * Builds the JMSSerializationContext from GET request params
     * and unsets the params from the request
     *
     * @return JMS\Serializer\SerializationContext
     */
    protected function buildSerializationContext(array $groups = array())
    {
        $context = new SerializationContext();

        if (0 === count($groups)) {
            $groups = $this->request->headers->get(self::HEADER_SERIALIZATION_GROUPS);
            $groups = explode(',', $groups);
        }

        // require that default group is always used
        $groups[] = 'default';

        $context->setGroups($groups);

        $context->enableMaxDepthChecks();

        return $context;
    }
}

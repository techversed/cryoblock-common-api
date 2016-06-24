<?php

namespace Carbon\ApiBundle\Listener;

use Monolog\Logger;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

class ApiResponseListener
{
    protected $logger;

    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }

    public function onKernelResponse(FilterResponseEvent $event)
    {
        $requestUri = $event->getRequest()->getRequestUri();

        if (!preg_match('/\/_uploader\/.*\/upload/', $requestUri)) {
            return;
        }

        $this->logger->info(sprintf('ApiResponseListener: Altering upload response for request %s', $requestUri));

        $response = $event->getResponse();

        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, apikey, Content-Disposition');
    }
}

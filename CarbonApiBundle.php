<?php

namespace Carbon\ApiBundle;

use Doctrine\Common\Annotations\AnnotationRegistry;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class CarbonApiBundle extends Bundle
{
    public function boot()
    {
        AnnotationRegistry::registerFile(__DIR__ . '/Annotation/CarbonApiAnnotations.php');
    }
}

<?php

namespace Carbon\ApiBundle\Service;

use Monolog\Logger;
use Oneup\UploaderBundle\Uploader\File\FileInterface;
use Oneup\UploaderBundle\Uploader\Naming\NamerInterface;

class UploadNamer implements NamerInterface
{
    protected $logger;

    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }

    public function name(FileInterface $file)
    {
        $originalClientName = $file->getClientOriginalName();

        $this->logger->info(sprintf('UploadNamer: naming %s', $originalClientName));

        $prependString = str_replace('.' . $file->getClientOriginalExtension(), '', $originalClientName);

        return sprintf('%s-%s.%s', $prependString, uniqid(), $file->getExtension());
    }
}

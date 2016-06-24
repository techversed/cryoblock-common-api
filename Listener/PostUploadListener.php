<?php

namespace Carbon\ApiBundle\Listener;

use Carbon\ApiBundle\Entity\Attachment;
use Oneup\UploaderBundle\Event\PostPersistEvent;
use Symfony\Component\DependencyInjection\Container;

class PostUploadListener
{
    protected $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function onPostUpload(PostPersistEvent $event)
    {
        $logger = $this->container->get('logger');

        $user = $this->container->get('security.context')->getToken()->getUser();

        $file = $event->getFile();

        $logger->info(sprintf('PostUploadListener: handling post upload for attachment %s', $file->getFilename()));

        $uploadDir = realpath($this->container->getParameter('carbon_api.upload_dir')) . DIRECTORY_SEPARATOR;

        $downloadPath = str_replace($uploadDir, '', $file->getRealPath());

        $oldAttachment = $user->getAvatarAttachment();

        $attachment = new Attachment();
        $attachment->setName($file->getFilename());
        $attachment->setDownloadPath($downloadPath);
        $attachment->setMimeType($file->getMimeType());
        $attachment->setSize($file->getSize());

        $user->setAvatarAttachment($attachment);

        $em = $this->container->get('doctrine.orm.default_entity_manager');

        // now delete old profile photo
        if ($oldAttachment) {

            unlink($uploadDir . $oldAttachment->getDownloadPath());
            $em->remove($oldAttachment);

        }

        $em->persist($attachment);
        $em->persist($user);
        $em->flush();
    }
}

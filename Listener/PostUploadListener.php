<?php

namespace Carbon\ApiBundle\Listener;

use Carbon\ApiBundle\Entity\User;
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
        $request = $event->getRequest();
        $objectId = $request->get('object_id');
        $objectClass = $request->get('object_class');
        $type = $event->getType();

        $logger = $this->container->get('logger');

        $user = $this->container->get('security.context')->getToken()->getUser();

        $file = $event->getFile();

        $logger->info(sprintf('PostUploadListener: handling post upload for attachment %s', $file->getFilename()));

        $uploadDir = realpath($this->container->getParameter('carbon_api.upload_dir')) . DIRECTORY_SEPARATOR;

        $downloadPath = str_replace($uploadDir, '', $file->getRealPath());

        $attachment = new Attachment();
        $attachment->setName($file->getFilename());
        $attachment->setDownloadPath($downloadPath);
        $attachment->setMimeType($file->getMimeType());
        $attachment->setSize($file->getSize());
        $attachment->setObjectId($objectId);
        $attachment->setObjectClass($objectClass);

        $em = $this->container->get('doctrine.orm.default_entity_manager');

        if ($objectClass === 'Carbon\ApiBundle\Entity\User' && $type === 'profile') {

            $oldAttachment = $user->getAvatarAttachment();

            $user->setAvatarAttachment($attachment);

            // now delete old profile photo
            if ($oldAttachment) {

                unlink($uploadDir . $oldAttachment->getDownloadPath());
                $em->remove($oldAttachment);

            }

            $em->persist($user);

        }

        $em->persist($attachment);
        $em->flush();
    }
}

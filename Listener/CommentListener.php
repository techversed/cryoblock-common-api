<?php

namespace Carbon\ApiBundle\Listener;

use Carbon\ApiBundle\Entity\Comment;
use Carbon\ApiBundle\Service\CryoblockMailer;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Bridge\Monolog\Logger;

class CommentListener
{
    public function __construct(CryoblockMailer $mailer, Logger $logger, $frontendUrl)
    {
        $this->mailer = $mailer;
        $this->logger = $logger;
        $this->frontendUrl = $frontendUrl;
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $comment = $args->getEntity();
        $em = $args->getEntityManager();

        if (($comment instanceof Comment) === FALSE) {
            return;
        }

        $content = $comment->getContent();
        $createdBy = $comment->getCreatedBy();

        $hasMention = preg_match_all("/@(\S*)/", $content, $matches);

        if (!$hasMention) {
            return;
        }

        $em = $args->getEntityManager();

        $mentionedUserEmails = $this->getMentionedUserEmails($em, $matches[1]);

        if (count($mentionedUserEmails) == 0 ) {
            return;
        }

        $params = array(
            'comment' => $comment,
            'fromUser' => $createdBy,
            'commentLink' => $this->getCommentLink($comment)
        );

        $this->sendMentionEmail($comment, $createdBy, $mentionedUserEmails, $params);
    }

    private function sendMentionEmail(Comment $comment, $fromUser, $toUsers, $params)
    {
        $this->mailer->send(
            sprintf('[cryoblock] %s mentioned you in %s %s', $fromUser->getFullName(), ucfirst($comment->getObjectType()), $comment->getObjectId()),
            'CarbonApiBundle::comment/mention.html.twig',
            $toUsers,
            $params
        );
    }

    private function getMentionedUserEmails(EntityManager $em, $userNames)
    {
        $mentionedUsers = $em->getRepository('CarbonApiBundle:User')->findBy(array('username'=> $userNames));

        $userEmails = array();
        foreach ($mentionedUsers as $mentionedUser) {
            $userEmails[$mentionedUser->getEmail()] = $mentionedUser->getFullName();
        }

        return $userEmails;
    }

    private function getCommentLink(Comment $comment)
    {
        $objectType = $comment->getObjectType();
        $objectId = $comment->getObjectId();

        return sprintf('http://%s%s/%s?commentId=%s', $this->frontendUrl, $objectType, $objectId, $comment->getId());
    }
}

<?php

namespace Carbon\ApiBundle\Service;

use Symfony\Component\DependencyInjection\Container;

class CryoblockMailer
{
    /**
     * @var Symfony\Component\DependencyInjection\Container
     */
    private $container;

    /**
     * Swift mailer
     *
     * @var \Swift_Mailer
     */
    private $mailer;

    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->mailer = $container->get('mailer');
    }

    public function send($subject, $template, $to, $params = array(), $from = null)
    {
        $content = $this->getTemplatingEngine()->render($template, $params);

        $fromArray = [];
        if (!$from) {

            $from = $this->getLoggedInUser();
            $fromEmail = $from->getEmail();
            $fromName = '=?UTF-8?B?' . base64_encode($from->getFullName() . ' ' . '(' . $this->getAppName() . ')') . '?=';
            $fromArray[$fromEmail] = $fromName;

        } else {
            $fromArray = $from;
        }

        $message = \Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom($fromArray)
            ->setTo($to)
            ->setBody($content, 'text/html')
        ;

        $this->mailer->send($message);
    }

    private function getTemplatingEngine()
    {
        return $this->container->get('templating');
    }

    private function getMailerUser()
    {
        return $this->container->getParameter('mailer_user');
    }

    private function getAppName()
    {
        return $this->container->getParameter('app.name');
    }

    private function getLoggedInUser()
    {
        return $this->container->get('security.context')->getToken()->getUser();

    }
}

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

    //Takes an array of group names looks up contact information for users in the set of groups and sends emails to all of them.
    public function sendToGroup($subject, $template, $toArray = array(), $params = array(), $from = null)
    {
        $em = $this->container->get('doctrine.orm.default_entity_manager');
        $sendTo = array();
        foreach ($toArray as $to){

            $groups = $em->getRepository("Carbon\ApiBundle\Entity\Group")->findBy(
                array('name' => $to)
            );
            $group = $groups[0];

            $users = $em->getRepository("Carbon\ApiBundle\Entity\UserGroup")->findBy(
                array('groupId' => $group->getId())
            );

            foreach ($users as $user){
                $sendTo[$user->getUser()->getEmail()] = $user->getUser()->getStringLabel();
            }

            print_r($sendTo);
        }

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
            ->setTo($sendTo)
            ->setBody($content, 'text/html')
        ;

        $this->mailer->send($message);
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

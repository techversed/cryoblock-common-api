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

    public function send($subject, $template, $to, $params = array(), $from = null, $groups = array())
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

        if (count($groups)) {

            $em = $this->container->get('doctrine.orm.default_entity_manager');

            $groups = $em->getRepository("Carbon\ApiBundle\Entity\Group")->findBy(
                array('name' => $groups)
            );

            foreach ($groups as $group) {
                foreach ($group->getGroupUsers() as $groupUser) {
                    $user = $groupUser->getUser();
                    $to[$user->getEmail()] = $user->getStringLabel();
                }
            }

        }

        if (count($to) == 0) {
            throw new \UnexpectedValueException('Can not send email to no one. $to array is empty');
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

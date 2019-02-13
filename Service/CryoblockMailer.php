<?php

namespace Carbon\ApiBundle\Service;

use Symfony\Component\DependencyInjection\Container;

/*

    Cryoblock mailer serves as a wrapper for swiftmailer (vendor code) and allows for us to have custom code which pulls data from psql and sends emails to the users of our system.



    Outstanding issues and things to consider:
        I am not sure if we really want to be throwing an exception near line 75 -- this might stop subsequent listeners from running -- check the behavior of exceptions in Doctrine and Symfony to see if this will end up presenting issues.
        If we start having groups which point to people outside of the vaccine center then we might want to move beyond having people.

*/

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

                    // If the user has been disabled the we do not want to send them emails -- similar filtering takes place in the Object Notification listener and it might be a good idea for us to move both things to the same location at some point.
                    if ($user->isEnabled() == true) {
                        $to[$user->getEmail()] = $user->getStringLabel();
                    }
                }
            }

        }

        // We may not even want to throw an exception -- may want to just return.
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

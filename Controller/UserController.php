<?php

namespace Carbon\ApiBundle\Controller;

use Carbon\ApiBundle\Controller\CarbonApiController;
use Doctrine\ORM\EntityNotFoundException;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\FOSUserEvents;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UserController extends CarbonApiController
{
    /**
     * @var string The namespace of the resource entity
     */
    const RESOURCE_ENTITY = "Carbon\ApiBundle\Entity\User";

    /**
     * @var string The form type for this resource
     */
    const FORM_TYPE = "user";

    /**
     * @Route("/user", name="user_options")
     * @Method("OPTIONS")
     *
     * @return [type] [description]
     */
    public function optionsAction()
    {
        $response = new Response();

        $data = array('success' => 'success');

        return $this->getJsonResponse(json_encode($data));
    }

    /**
     * @Route("/user", name="user_get")
     * @Method("GET")
     *
     * @return [type] [description]
     */
    public function getAction()
    {
        return parent::handleGet();
    }

    /**
     * Handles the HTTP PUT request for the user entity
     *
     * @todo  figure out why PUT method has no request params
     * @Route("/user", name="user_post")
     * @Method("POST")
     * @return [type] [description]
     */
    public function handlePost()
    {
        /** @var $userManager \FOS\UserBundle\Model\UserManagerInterface */
        $userManager = $this->get('fos_user.user_manager');

        /** @var $dispatcher \Symfony\Component\EventDispatcher\EventDispatcherInterface */
        $dispatcher = $this->get('event_dispatcher');

        $user = $userManager->createUser();
        $user->setEnabled(true);

        $request = $this->getRequest();

        $event = new GetResponseUserEvent($user, $request);
        $dispatcher->dispatch(FOSUserEvents::REGISTRATION_INITIALIZE, $event);

        if (null !== $event->getResponse()) {
            return $event->getResponse();
        }

        $form = $this->createForm('user', $user);

        $form->submit(json_decode($this->getRequest()->getContent(), true));

        if ($form->isValid()) {

            $event = new FormEvent($form, $request);

            $dispatcher->dispatch(FOSUserEvents::REGISTRATION_SUCCESS, $event);

            $userManager->updateUser($user);

            $response = $this->getJsonResponse(json_encode(array('success' => 'success')));

            $dispatcher->dispatch(FOSUserEvents::REGISTRATION_COMPLETED, new FilterUserResponseEvent($user, $request, $response));

            return $response;

        }

        $errors = array();
        foreach ($form->getErrors(true) as $error) {
            $errors[$error->getOrigin()->getName()] = $error->getMessage();
        }

        return $this->getJsonResponse(json_encode($errors), 405);
    }

    /**
     * Handles the HTTP PUT request for the user entity
     *
     * @todo  figure out why PUT method has no request params
     * @Route("/user", name="user_put")
     * @Method("PUT")
     * @return [type] [description]
     */
    public function handlePut()
    {
        return parent::handlePut();
    }

    /**
     * @Route("/user", name="user_delete")
     * @Method("DELETE")
     *
     * @return Response
     */
    public function deleteAction()
    {
        $id = $this->getRequest()->get('id');

        if (!$id) {

            throw new \RuntimeException('User id not specified in request');

        }

        $em = $this->getEntityManager();
        $user = $this->getEntityRepository()->find($id);

        if (!$user) {
            throw new EntityNotFoundException(sprintf('User %s not found', $id));
        }

        if ($this->getUser()->getId() === (int) $id) {
            throw new \RuntimeException('You cannot delete yourself.');
        }

        $user->setEnabled(false);
        $em->flush();

        return $this->getJsonResponse(json_encode(array('success' => 'sucess')));
    }
}

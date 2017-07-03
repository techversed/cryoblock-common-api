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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
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
     * @Route("/user", name="user_get")
     * @Method("GET")
     * @Security("has_role('ROLE_USER')")
     *
     * @return Response
     */
    public function getAction()
    {
        return parent::handleGet();
    }

    /**
     * Handles the HTTP PUT request for the user entity
     *
     * @Route("/user", name="user_post")
     * @Method("POST")
     * @Security("has_role('ROLE_ADMIN')")
     *
     * @return Response
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
     * @Route("/user", name="user_put")
     * @Method("PUT")
     * @Security("has_role('ROLE_ADMIN')")
     *
     * @return Response
     */
    public function handlePut()
    {
        return parent::handlePut();
    }

    /**
     * @Route("/user", name="user_delete")
     * @Method("DELETE")
     * @Security("has_role('ROLE_ADMIN')")
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

        return $this->getJsonResponse(json_encode(array('success' => 'success')));
    }

    /**
     * @Route("/user/password-reset", name="user_password_reset")
     * @Method("POST")
     *
     * @return Response
     */
    public function passwordResetAction(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $email = $data['email'];

        if (!$email) {
            throw new \RuntimeException('No username specified.');
        }

        $user = $this->get('fos_user.user_manager')->findUserByUsernameOrEmail($email);

        if (!$user) {
            throw new \RuntimeException(sprintf('No user with email %s found.', $email));
        }

        if ($user->isPasswordRequestNonExpired($this->container->getParameter('fos_user.resetting.token_ttl'))) {
            throw new \RuntimeException('Password reset already requested');
        }

        if (null === $user->getConfirmationToken()) {
            /** @var $tokenGenerator \FOS\UserBundle\Util\TokenGeneratorInterface */
            $tokenGenerator = $this->get('fos_user.util.token_generator');
            $user->setConfirmationToken($tokenGenerator->generateToken());
        }

        $template = $this->getParameter('fos_user.resetting.email.template');
        $url = $this->getParameter('frontend_password_reset_url') . '/' . $user->getConfirmationToken();
        $from = $this->getParameter('fos_user.resetting.email.from_email');
        $templating = $this->get('templating');

        // $rendered = $templating->render($template, array(
        //     'user' => $user,
        //     'confirmationUrl' => $url
        // ));

        $subject = 'Password Reset';

        $this->get('carbon_api.mailer')->send($subject, $template, $user->getEmail(), array(
            'user' => $user,
            'confirmationUrl' => $url,
        ), $from);

        $this->getEntityManager()->flush();

        return $this->getJsonResponse(json_encode(array('success' => 'success')));
    }

    /**
     * @Route("/user/password-reset-confirm", name="user_password_reset_confirm")
     * @Method("POST")
     *
     * @return Response
     */
    public function passwordResetConfirmAction(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $token = $data['token'];
        $password = $data['password'];
        $um = $this->get('fos_user.user_manager');

        $user = $um->findUserByConfirmationToken($token);

        if (!$user) {
            throw new \RuntimeException(sprintf('No user found with confirmation token %s.', $token));
        }

        if (!$password || $password == '') {
            throw new \RuntimeException('Password must not be empty');
        }

        $user->setPlainPassword($password);
        $um->updatePassword($user);

        $this->getEntityManager()->flush();

        return $this->getJsonResponse(json_encode(array('success' => 'success')));
    }

    /**
     * @Route("/user/password/reset", name="user_password_change")
     * @Method("POST")
     * @Security("has_role('ROLE_USER')")
     *
     * @return Response
     */
    public function passwordChangeAction(Request $request)
    {
        $data = json_decode($request->getContent(), true);

        $currentPassword = $data['currentPassword'];

        $password = $data['password'];

        $um = $this->get('fos_user.user_manager');

        $user = $this->getUser();

        if (!$password || $password == '') {
            throw new \RuntimeException('Password must not be empty');
        }

        $encoder_service = $this->get('security.encoder_factory');
        $encoder = $encoder_service->getEncoder($user);
        $encoded_pass = $encoder->encodePassword($currentPassword, $user->getSalt());

        if ($encoded_pass != $user->getPassword()) {
            throw new AccessDeniedHttpException("Password does not match password on record");
        }

        $user->setPlainPassword($password);
        $um->updatePassword($user);

        $this->getEntityManager()->flush();

        return $this->getJsonResponse(json_encode(array('success' => 'success')));
    }

    /**
     * @Route("/user/password/admin-reset", name="admin_password_change")
     * @Method("POST")
     * @Security("has_role('ROLE_ADMIN')")
     *
     * @return Response
     */
    public function passwordAdminResetAction(Request $request)
    {
        $data = json_decode($request->getContent(), true);

        $um = $this->get('fos_user.user_manager');

        $password = $data['password'];

        $userArray = $data['user'];

        $user = $this->get('fos_user.user_manager')->findUserByUsernameOrEmail($userArray['username']);

        if (!$password || $password == '') {
            throw new \RuntimeException('Password must not be empty');
        }

        $user->setPlainPassword($password);
        $um->updatePassword($user);

        $this->getEntityManager()->flush();

        return $this->getJsonResponse(json_encode(array('success' => 'success')));
    }
}

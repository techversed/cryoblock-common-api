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
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/*
    One issue that I created in this file is that common really should not refer to 'ROLE_UNDERGRAD_STUDENT_WORKER' since this is specific to the crowelab user permissions setup. -- In the long term I think that it would be a good idea to implement a series ofa ccounts which do not have the full permissions of a regular user in case you want to share some of the data with collaborators -- you may want to have collaborator accounts.

    VIOLATION - I placed the sample cloning in the sample controller due to the fact that other related functions were already stored there --- it is modifying a property located on user though so that is not proper


*/

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
     * @Security("has_role('ROLE_USER') || has_role('ROLE_UNDERGRAD_STUDENT_WORKER')")
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
     * @Security("has_role('ROLE_USER')")
     *
     * @return Response
     */
    public function handlePut()
    {
        $userEditId = $this->getGrid()->getResult($this->getEntityRepository());
        $userEditId = $userEditId['data'][0]->getId();

        if ($this->getUser()->hasRole('ROLE_ADMIN') || $this->getUser()->getId() == $userEditId) {
            return parent::handlePut();
        }
        else {
            return $this->getJsonResponse($this->getSerializationHelper()->serialize(
            array('violations' => array(array(
                'Sorry, you do not have permission to edit this User.',
            )))
        ), 400);
        }
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

        $loggedInUser = $this->getUser();
        $isAdmin = $loggedInUser->hasRole('ROLE_ADMIN');
        $userToChange = $this->getEntityRepository('Carbon\ApiBundle\Entity\User')->find($data['userId']);

        if (!$isAdmin && ($loggedInUser->getId() !== $userToChange->getId())) {
            throw new \RuntimeException('You don not have permission to do this');
        }

        $password = $data['password'];

        $um = $this->get('fos_user.user_manager');

        if (!$password || $password == '') {
            throw new \RuntimeException('Password must not be empty');
        }

        // a user is doing this
        if (!$isAdmin) {

            $currentPassword = $data['currentPassword'];

            $encoder_service = $this->get('security.encoder_factory');
            $encoder = $encoder_service->getEncoder($userToChange);
            $encoded_pass = $encoder->encodePassword($currentPassword, $userToChange->getSalt());

            if ($encoded_pass != $userToChange->getPassword()) {
                throw new AccessDeniedHttpException("Password does not match password on record");
            }
        }

        $userToChange->setPlainPassword($password);

        $um->updatePassword($userToChange);

        $this->getEntityManager()->flush();

        return $this->getJsonResponse(json_encode(array('success' => 'success')));
    }

}

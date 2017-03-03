<?php

namespace Carbon\ApiBundle\Controller;

use FOS\UserBundle\Controller\SecurityController as BaseController;
use JMS\Serializer\SerializationContext;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class LoginController extends BaseController
{
    public function authenticateAction(Request $request)
    {
        $content = json_decode($request->getContent(), true);

        $username = $content['username'];
        $password = $content['password'];

        if (!isset($username) || !isset($password)){
            throw new BadRequestHttpException("You must pass username and password fields");
        }

        $um = $this->get('fos_user.user_manager');
        $user = $um->findUserByUsernameOrEmail($username);

        if (!$user instanceof \Carbon\ApiBundle\Entity\User) {
            throw new AccessDeniedHttpException("No matching user account found");
        }

        if (!$user->isEnabled()) {
            throw new AccessDeniedHttpException(sprintf("User %s is not enabled.", $user->getFullName()));
        }

        $encoder_service = $this->get('security.encoder_factory');
        $encoder = $encoder_service->getEncoder($user);
        $encoded_pass = $encoder->encodePassword($password, $user->getSalt());

        if ($encoded_pass != $user->getPassword()) {
            throw new AccessDeniedHttpException("Password does not match password on record");
        }

        $userData = $this->get('carbon_api.serialization_helper')
            ->serialize($user, array('default'))
        ;

        $userData = json_decode($userData, true);
        $userData['apiKey'] = $user->getApiKey();
        $userData = json_encode($userData);

        $response = new Response($userData);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
}

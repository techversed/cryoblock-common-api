<?php

namespace Carbon\ApiBundle\Controller;

use Carbon\ApiBundle\Controller\CarbonApiController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\NotFoundHttpException;
use Carbon\ApiBundle\Entity\Production\BaseRequest;
use JMS\Serializer\SerializationContext;

class UserObjectNotificationController extends CarbonApiController
{
    /**
     * @var string The namespace of the resource entity
     */
    const RESOURCE_ENTITY = "Carbon\ApiBundle\Entity\UserObjectNotification";

    /**
     * @var string The form type for this resource
     */
    const FORM_TYPE = "user_object_notification";

    /**
     * @Route("/cryoblock/user-object-notification", name="user_object_notification_get")
     * @Method("GET")
     *
     * @return Response
     */
    public function getAction()
    {
        return parent::handleGet();
    }

    /**
     * @Route("/cryoblock/user-object-notification", name="user_object_notification_post")
     * @Method("POST")
     *
     * @return Response
     */
    public function handlePost()
    {
        return parent::handlePost();
    }

    /**
     * @Route("/cryoblock/user-object-notification", name="user_object_notification_put")
     * @Method("PUT")
     *
     * @return Response
     */
    public function handlePut()
    {
        return parent::handlePut();
    }

    /**
     * @Route("/cryoblock/user-object-notification", name="user_object_notification_delete")
     * @Method("DELETE")
     *
     * @return Response
     */
    public function handleDelete()
    {
        return parent::handleDelete();
    }

    /**
     * @Route("/cryoblock/user-object-notification/watched-requests", name="user_object_notification_watching_get")
     * @Method("GET")
     *
     * @return Response
     */
    public function getUserWatchedAction()
    {

        $em = parent::getEntityManager();
        $objNotRep = parent::getEntityRepository();

        $notifications = $objNotRep->findBy(array( 'user' => $this->getUser()));

        $entReps = array();

        $selectedNotifications = array();

        foreach ($notifications as $notification) {

            $entityDetail = $notification->getEntityDetail();
            $objectClassName = $entityDetail->getObjectClassName();

            if (!$entityDetail->getInNotifications() || $notification->getDismissed()) {
                continue;
            }

            if (!array_key_exists($objectClassName, $entReps)) {
                $entReps[$objectClassName] = $em->getRepository($objectClassName);
            }

            if ($notification->getEntityId() != null) {
                $notification->setEntity($entReps[$objectClassName]->find($notification->getEntityId()));
                $selectedNotifications[] = $notification;
            }

        }

        $data = $this->getSerializationHelper()->serialize($selectedNotifications);
        return $this->getJsonResponse($data);


    }
}

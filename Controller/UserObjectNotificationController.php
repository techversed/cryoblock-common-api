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

use Carbon\ApiBundle\Entity\UserObjectNotification;

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
        $this->checkPermission('POST');

        $request = $this->getRequest();
        $requestData = json_decode($request->getContent(), true);

        if (($contentType = $request->getContentType()) !== 'json') {
            return new Response(sprintf(
                'Content type must be json, %s given',
                $contentType
            ), 415);
        }

        $entityClass = $this->getEntityClass();
        $entity = new $entityClass();

        if (!defined('static::FORM_TYPE')) {
            throw new \LogicException('No form type specified. Did you add the FORM_TYPE const to your resource controller?');
        }

        $form = $this->createForm(static::FORM_TYPE, $entity);

        $form->submit($requestData);

        if (!$form->isValid()) {
            return $this->getFormErrorResponse($form);
        }

        $newEntity = new UserObjectNotification();

        if(array_key_exists('onCreate', $requestData)) {
            $newEntity->setOnCreate(true);
        }
        if(array_key_exists('onUpdate', $requestData)) {
            $newEntity->setOnUpdate(true);
        }
        if(array_key_exists('onDelete', $requestData)) {
            $newEntity->setOnDelete(true);
        }

        $newEntity->setUser($this->getUser());

        if(array_key_exists('entityDetail', $requestData)) {
            $newEntity->setEntityId(-1);
            $newEntity->setLinkedEntityDetail($this->getEntityManager()->getRepository('Carbon\ApiBundle\Entity\EntityDetail')->find($requestData['entityDetail']['id']));
            $newEntity->setLinkedEntityDetailId($requestData['entityDetail']['id']);
        } else {
            $newEntity->setEntityId($requestData['entityId']);
            $newEntity->setLinkedEntityDetail($this->getEntityManager()->getRepository('Carbon\ApiBundle\Entity\EntityDetail')->find($requestData['linkedEntityDetail']['id']));
        }

        $this->getEntityManager()->persist($newEntity);
        $this->getEntityManager()->flush();

        return $this->getJsonResponse($this->getSerializationHelper()->serialize($newEntity));
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

            $entityDetail = $notification->getLinkedEntityDetail();
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

        $res = array(
            'page' => 1,
            'perPage' => count($selectedNotifications),
            'hasNextPage' => false,
            'unpaginatedTotal' => count($selectedNotifications),
            'paginatedTotal' => count($selectedNotifications),
            'data' => json_decode($data)
        );

        return $this->getJsonResponse(json_encode($res));
    }

    /**
     * @Route("/cryoblock/profile-object-notification/watched-requests/{id}", name="profile_object_notification_watching_get")
     * @Method("GET")
     *
     * @return Response
     */
    public function getProfileWatchedAction($id)
    {

        $em = parent::getEntityManager();
        $objNotRep = parent::getEntityRepository();

        $notifications = $objNotRep->findBy(array( 'userId' => $id));

        $entReps = array();

        $selectedNotifications = array();

        foreach ($notifications as $notification) {

            $entityDetail = $notification->getLinkedEntityDetail();
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

        $res = array(
            'page' => 1,
            'perPage' => count($selectedNotifications),
            'hasNextPage' => false,
            'unpaginatedTotal' => count($selectedNotifications),
            'paginatedTotal' => count($selectedNotifications),
            'data' => json_decode($data)
        );

        return $this->getJsonResponse(json_encode($res));
    }

    /**
     * @Route("/cryoblock/user-object-notification/dismiss-watche-requests", name="user_object_notification_dismiss_all_post")
     * @Method("POST")
     *
     * @return Response
     */
    public function dismissAllNotifications()
    {

        $em = parent::getEntityManager();
        $objNotRep = parent::getEntityRepository();

        $notifications = $objNotRep->findBy(array( 'user' => $this->getUser()));

        $entReps = array();

        $selectedNotifications = array();

        foreach ($notifications as $notification) {

            $entityDetail = $notification->getLinkedEntityDetail();
            $objectClassName = $entityDetail->getObjectClassName();

            if (!$entityDetail->getInNotifications() || $notification->getDismissed()) {
                continue;
            }

            $notification->setDismissed(true);

        }

        $em->flush();

        $data = array('success' => 'success');
        return $this->getJsonResponse(json_encode($data));

    }


}

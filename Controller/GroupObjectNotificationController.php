<?php

namespace Carbon\ApiBundle\Controller;

use Carbon\ApiBundle\Controller\CarbonApiController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\NotFoundHttpException;

use Carbon\ApiBundle\Entity\GroupObjectNotification;

class GroupObjectNotificationController extends CarbonApiController
{
    /**
     * @var string The namespace of the resource entity
     */
    const RESOURCE_ENTITY = "Carbon\ApiBundle\Entity\GroupObjectNotification";

    /**
     * @var string The form type for this resource
     */
    const FORM_TYPE = "group_object_notification";

    /**
     * @Route("/cryoblock/group-object-notification", name="group_object_notification_get")
     * @Method("GET")
     *
     * @return Response
     */
    public function getAction()
    {
        return parent::handleGet();
    }

    /**
     * Handles the HTTP POST request for the group entity
     *
     * @Route("/cryoblock/group-object-notification", name="group_object_notification_post")
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

                $form->submit(json_decode($request->getContent(), true));

                if (!$form->isValid()) {
                    return $this->getFormErrorResponse($form);
                }

                $newEntity = new GroupObjectNotification();

                if(array_key_exists('onCreate', $requestData)) {
                    $newEntity->setOnCreate(true);
                }
                if(array_key_exists('onUpdate', $requestData)) {
                    $newEntity->setOnUpdate(true);
                }
                if(array_key_exists('onDelete', $requestData)) {
                    $newEntity->setOnDelete(true);
                }

                $newEntity->setLinkedEntityDetail($this->getEntityManager()->getRepository('Carbon\ApiBundle\Entity\EntityDetail')->find($requestData['entityDetail']['id']));
                $newEntity->setLinkedEntityDetailId($requestData['entityDetail']['id']);

                $this->getEntityManager()->persist($newEntity);
                $this->getEntityManager()->flush();

                $this->getEntityManager()->persist($entity);
                $this->getEntityManager()->flush();

                return $this->getJsonResponse($this->getSerializationHelper()->serialize($entity));
    }

    /**
     * Handles the HTTP PUT request for the group entity
     *
     * @Route("/cryoblock/group-object-notification", name="group_object_notification_put")
     * @Method("PUT")
     *
     * @return Response
     */
    public function handlePut()
    {
        return parent::handlePut();
    }
}

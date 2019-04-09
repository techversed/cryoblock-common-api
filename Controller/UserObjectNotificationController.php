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
     * @Route("/cryoblock/user-object-notification/user/{userId}", name="user_object_notification_watching_get")
     * @Method("GET")
     *
     * @return Response
     */
    public function getUserWatchedAction($userId)
    {

        $em = parent::getEntityManager();
        $objNotRep = parent::getEntityRepository();

        $notifications = $objNotRep->findBy(array( 'user' => $this->getUser()));
        $entReps = array();

        foreach ($notifications as $notification) {
            if (array_key_exists(..., $entReps ) ) {

            }
        }


        // $this->getSerializationHelper()->serialize();
        // return $this->getJsonResponse($data);



        $res = new Response();

        return $res;





        // We could probably handle this using a serializer listener

        // echo count($notifications);
        // instanceof BaseRequest


        // echo count($objNotRep->getUser());
        // echo $this->getUser()->getId();
        // return
        // $objNotRep->findBy("user


        // $this->checkPermission('GET');

        // $entityRepository = $this->getEntityRepository();

        // $request = $this->getRequest();

        // $isDataTableRequest = $this->isDataTableRequest($request);

        // $data = $this->getSerializationHelper()->serialize(
        //     $this->getGrid($isDataTableRequest)->getResult($this->getEntityRepository())
        // );

        // return $this->getJsonResponse($data);




        // $entityRepository = $this->getEntityRepository();


        // $query = 'SELECT * from cryoblock.user_object_notification WHERE user_id = :user_id AND entity_id is NOT NULL';
        // $stmt = $this->getEntityManager()->getConnection()->prepare($query);
        // $stmt->execute(array(
            // 'user_id' => $userId
        // ));

        // $results = $stmt->fetchAll();

        // $watchedObjects = [];

        // foreach ($results as $result) {

            // $route = $result->getEntityDetail->getObjectUrl();

            // $watchedObjects[] = $route + '/' + $result['entity_id'];
        // }

        // return parent::handleGet();
    }
}

<?php

namespace Carbon\ApiBundle\Controller;

use Carbon\ApiBundle\Controller\CarbonApiController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class AttachmentController extends CarbonApiController
{
    /**
     * @var string The namespace of the resource entity
     */
    const RESOURCE_ENTITY = "Carbon\ApiBundle\Entity\Attachment";

    /**
     * Handles the HTTP get request for the attachment entity
     *
     * @Route("/attachment", name="attachment_get")
     * @Method("GET")
     * @Security("is_granted('ROLE_USER')")
     *
     * @return Response
     */
    public function handleGet()
    {
        return parent::handleGet();
    }

    /**
     * Handles the HTTP DELETE request for the attachment entity
     *
     * @Route("/attachment", name="attachment_delete")
     * @Method("DELETE")
     * @Security("is_granted('ROLE_USER')")
     *
     * @return Response
     */
    public function handleDelete()
    {
        $gridResult = $this->getGrid()->getResult($this->getEntityRepository());

        if (($foundResultsCount = count($gridResult['data'])) > 1 || $foundResultsCount === 0) {
            return new Response(sprintf(
                'Delete method expects one entity to be found for deletion, %s found from GET params',
                $foundResultsCount
            ), 401);
        }

        $attachment = $gridResult['data'][0];

        $uploadDir = realpath($this->container->getParameter('carbon_api.upload_dir')) . DIRECTORY_SEPARATOR;

        unlink($uploadDir . $attachment->getDownloadPath());

        $this->getEntityManager()->remove($attachment);
        $this->getEntityManager()->flush();

        return $this->getJsonResponse(json_encode(array('success' => true)));
    }
}

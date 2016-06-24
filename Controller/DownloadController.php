<?php

namespace Carbon\ApiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class DownloadController extends Controller
{
    public function optionsAction()
    {
        $response = new Response();

        $data = array('success' => 'success');

        $response->setContent(json_encode($data, true));

        $response->headers->set('Content-Type', 'application/json');
        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->headers->set('Access-Control-Allow-Headers', 'apikey');

        return $response;
    }

    /**
     * Download an attachment
     *
     * @return Symfony\Component\HttpFoundation\Response
     */
    public function downloadAction($attachmentId)
    {
        $attachment = $this->getEntityRepository('Carbon\ApiBundle\Entity\Attachment')
            ->find($attachmentId)
        ;

        if (!$attachment) {
            throw new \InvalidArgumentException(
                sprintf('Attachment %s not found', $attachmentId)
            );
        }

        $downloadPath = $attachment->getDownloadPath();
        $realPath = $this->getParameter('carbon_api.upload_dir') . DIRECTORY_SEPARATOR . $downloadPath;

        if (!file_exists($realPath)) {
            throw new \RuntimeException(
                sprintf('File %s does not exist', $realPath)
            );
        }

        $response = new Response(file_get_contents($realPath));
        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->headers->set('Access-Control-Allow-Headers', 'apikey');
        $response->headers->set('Content-Type', $attachment->getMimeType());

        return $response;
    }

    /**
     * Get the doctrine entity manager
     *
     * @return Doctrine\ORM\EntityManager
     */
    protected function getEntityManager()
    {
        return $this->get('doctrine.orm.default_entity_manager');
    }

    /**
     * Get the repository class for the given resource/entity
     *
     * @return Doctrine\ORM\EntityRepository
     */
    protected function getEntityRepository($entity)
    {
        return $this->getEntityManager()->getRepository($entity);
    }

    /**
     * Return a json response with content of provided json data
     *
     * @param  string $data json string
     * @return Symfony\Component\HttpFoundation\Response
     */
    protected function getJsonResponse($data)
    {
        return new Response($data, 200, array('Content-Type' => 'application/json'));
    }
}

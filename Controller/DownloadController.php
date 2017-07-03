<?php

namespace Carbon\ApiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DownloadController extends Controller
{
    /**
     * Download an attachment
     *
     * @return Symfony\Component\HttpFoundation\Response
     */
    public function downloadAction($attachmentId)
    {
        set_time_limit(0);

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

        // $response = new Response(file_get_contents($realPath));
        $response = new StreamedResponse();
        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->headers->set('Access-Control-Allow-Headers', 'apikey');
        $response->headers->set('Content-Type', $attachment->getMimeType());
        $response->headers->set('Content-Disposition','inline; filename="' . $attachment->getName() . '"');
        $response->headers->set('Content-Length', filesize($realPath));

        $response->setCallback(function () use ($realPath) {

            $size = filesize($realPath);
            $offset = 0;
            $length = $size;
            $size = filesize($realPath);
            $chunksize = 8 * (1024 * 1024); //8MB (highest possible fread length)

            if ($size > $chunksize) {

                $handle = fopen($realPath, 'rb');
                $buffer = '';

                while (!feof($handle) && (connection_status() === CONNECTION_NORMAL)) {

                    $buffer = fread($handle, $chunksize);
                    echo $buffer;
                    ob_flush();
                    flush();

                }

                if(connection_status() !== CONNECTION_NORMAL) {
                    echo "Connection aborted";
                }

                fclose($handle);

            } else {

                ob_clean();
                flush();
                readfile($realPath);

            }

        });

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

<?php

namespace Carbon\ApiBundle\Controller\Storage;

use AppBundle\Entity\Sample;
use Carbon\ApiBundle\Controller\CarbonApiController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\NotFoundHttpException;

class BaseSampleController extends CarbonApiController
{
    /**
     * @var string The namespace of the resource entity
     */
    const RESOURCE_ENTITY = "AppBundle\Entity\Storage\Sample";

    /**
     * @var string The form type for this resource
     */
    const FORM_TYPE = "sample";

    /**
     * Handles the HTTP get request for the division entity
     *
     * @Route("/storage/sample", name="sample_get")
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
     * Handles the HTTP get request for the card entity
     *
     * @Route("/storage/sample", name="sample_post")
     * @Method("POST")
     *
     * @return Response
     */
    public function handlePost()
    {
        return parent::handlePost();
    }

    /**
     * Handles the HTTP PUT request for the card entity
     *
     * @todo  figure out why PUT method has no request params
     * @Route("/storage/sample", name="sample_put")
     * @Method("PUT")
     *
     * @return Response
     */
    public function handlePut()
    {
        $gridResult = $this->getGrid()->getResult($this->getEntityRepository());

        if (($foundResultsCount = count($gridResult['data'])) > 1 || $foundResultsCount === 0) {
            return new Response(sprintf(
                'Delete method expects one entity to be found for deletion, %s found from GET params',
                $foundResultsCount
            ), 401);
        }

        $sample = $gridResult['data'][0];

        if ($sample->getDivision()) {
            $canEdit = $this->getEntityManager()->getRepository('AppBundle\Entity\Storage\Division')
                ->canUserEdit($sample->getDivision(), $this->getUser())
            ;
        } else {
            $canEdit = true;
        }

        if (!$canEdit) {
            return $this->getJsonResponse($this->getSerializationHelper()->serialize(
                array('violations' => array(array(
                    'Sorry, you do not have permission to edit sample ' . $sample->getId(),
                )))
            ), 400);
        }

        return parent::handlePut();
    }

    /**
     * Handles the HTTP DELETE request for the card entity
     *
     * @Route("/storage/sample", name="sample_delete")
     * @Method("DELETE")
     *
     * @return Response
     */
    public function handleDelete()
    {
        return parent::handleDelete();
    }

    /**
     * Handles the HTTP POST request for moving a division
     *
     * @Route("/storage/sample/storage-remove", name="sample_storage_remove")
     * @Method("POST")
     *
     * @return Response
     */
    public function storageRemove()
    {
        $content = (json_decode($this->getRequest()->getContent(), true));
        $repo = $this->getEntityRepository();

        $sampleIds = $content['sampleIds'];
        $status = $content['status'];

        foreach ($sampleIds as $sampleId) {
            $sample = $repo->find($sampleId);
            $sample->setDivision(null);
            $sample->setDivisionId(null);
            $sample->setDivisionRow(null);
            $sample->setDivisionColumn(null);
            $sample->setStatus($status);
        }

        $this->getEntityManager()->flush();

        return $this->getJsonResponse(json_encode(array('success' => 'success')));
    }

    /**
     * Handles the HTTP POST request for moving a division
     *
     * @Route("/storage/sample/storage-move", name="sample_storage_move")
     * @Method("POST")
     *
     * @return Response
     */
    public function storageMove()
    {
        $sampleMoveMap = (json_decode($this->getRequest()->getContent(), true));
        $repo = $this->getEntityRepository();

        foreach ($sampleMoveMap as $map) {

            $sample = $repo->find($map['id']);

            $form = $this->createForm('sample', $sample);
            $form->submit($map);

            if (!$form->isValid()) {

                return $this->getFormErrorResponse($form);

            }

        }


        $this->getEntityManager()->flush();

        return $this->getJsonResponse(json_encode(array('success' => 'success')));
    }

    /**
     * Handles the HTTP POST request for cloning a sample
     *
     * @Route("/storage/sample/{parentSampleId}/clone", name="sample_storage_clone")
     * @Method("POST")
     *
     * @return Response
     */
    public function storageClone($parentSampleId)
    {
        $sampleCloneMap = (json_decode($this->getRequest()->getContent(), true));
        $repo = $this->getEntityRepository();
        $parentSample = $repo->find($parentSampleId);
        $em = $this->getEntityManager();

        foreach ($sampleCloneMap as $map) {

            $newSample = clone $parentSample;

            $em->detach($newSample);
            $em->persist($newSample);

            foreach ($newSample->getSampleTags() as $sampleTag) {
                $em->persist($sampleTag);
            }

            foreach ($newSample->getProjectSamples() as $projectSample) {
                $em->persist($projectSample);
            }

            $newSample->setDivisionColumn($map['divisionColumn']);
            $newSample->setDivisionRow($map['divisionRow']);
            $newSample->setCreatedBy($this->getUser());
            $newSample->setCreatedAt(new \DateTime());
            $newSample->setUpdatedBy($this->getUser());
            $newSample->setUpdatedAt(new \DateTime());

        }

        $em->flush();

        return $this->getJsonResponse(json_encode(array('success' => 'success')));
    }
}

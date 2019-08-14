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
use Symfony\Component\HttpKernel\Exception\HttpException;

/*

    VIOLATION - I am modifying the user entity here -- if we were doing this perfectly we would really only edit samples from this location


*/

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


    // Might also be  a good idea to do a check to see if the well is taken. It should not be taken since they would need to select it on the frontend.
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



        $divisionRepository = $em->getRepository('AppBundle\Entity\Storage\Division');

        // Called when the division is dimensionless
        if (array_key_exists('count', $sampleCloneMap)) {

            for ($i = 1; $i <= $sampleCloneMap['count']; $i++) {

                $div = $divisionRepository->find($sampleCloneMap['divisionId']);

                $newSample = clone $parentSample;

                // CHANGE THIS
                if(!$divisionRepository->canUserEdit($div, $this->getUser()))
                {
                    $message = 'You do not have permission to edit the current division.';
                    $headers = array('CB-DELETE-MESSAGE' => $message);
                    throw new HttpException(403, $message, null, $headers);
                }

                // CHANGE THIS
                if(!$divisionRepository->allowsSamplePlacement($div, $newSample))
                {
                    $message = 'The Storage Container type or Sample type is not allowed in the selected division.';
                    $headers = array('CB-DELETE-MESSAGE' => $message);
                    throw new HttpException(403, $message, null, $headers);
                }

                // Add a check to make sure that the cell is still available

                $em->detach($newSample);
                $em->persist($newSample);

                foreach ($newSample->getSampleTags() as $sampleTag) {
                    $em->persist($sampleTag);
                }

                foreach ($newSample->getProjectSamples() as $projectSample) {
                    $em->persist($projectSample);
                }

                $newSample->setDivision($div);
                $newSample->setDivisionColumn(null);
                $newSample->setDivisionRow(null);
                $newSample->setCreatedBy($this->getUser());
                $newSample->setCreatedAt(new \DateTime());
                $newSample->setUpdatedBy($this->getUser());
                $newSample->setUpdatedAt(new \DateTime());

            }
        }
        // Called once for each past when the division has dimension
        else {

            foreach ($sampleCloneMap as $map) {

                // Check user permissions
                // Check sampletype and storage container permissions

                $div = $divisionRepository->find($map['divisionId']);

                $newSample = clone $parentSample;

                // asdlfkjsdlfkj

                // CHANGE THIS
                if (!$divisionRepository->canUserEdit($div, $this->getUser())) {

                    // return new Response(sprintf('You do not have permission to edit the selected division.'), 403);

                    $message = 'You do not have permission to edit the current division.';
                    $headers = array('CB-DELETE-MESSAGE' => $message);
                    throw new HttpException(403, $message, null, $headers);

                }

                // CHANGE THIS
                if (!$divisionRepository->allowsSamplePlacement($div, $newSample->getSampleType(), $newSample->getStorageContainer())) {

                    // return new Response(sprintf('The Storage Container type or Sample type is not allowed in the selected division.'), 403);

                    $message = 'The Storage Container type or Sample type is not allowed in the selected division.';
                    $headers = array('CB-DELETE-MESSAGE' => $message);
                    throw new HttpException(403, $message, null, $headers);

                }

                // Add a check to make sure that the cell is still available

                $em->detach($newSample);
                $em->persist($newSample);

                foreach ($newSample->getSampleTags() as $sampleTag) {
                    $em->persist($sampleTag);
                }

                foreach ($newSample->getProjectSamples() as $projectSample) {
                    $em->persist($projectSample);
                }

                $newSample->setDivision($div);
                $newSample->setDivisionColumn($map['divisionColumn']);
                $newSample->setDivisionRow($map['divisionRow']);
                $newSample->setCreatedBy($this->getUser());
                $newSample->setCreatedAt(new \DateTime());
                $newSample->setUpdatedBy($this->getUser());
                $newSample->setUpdatedAt(new \DateTime());

            }

        }

        $em->flush();

        return $this->getJsonResponse(json_encode(array('success' => 'success')));
    }

    // VIOLATION -- This should really not be in common
    // VIOLATION -- This should probably be on user since it is really updating a property which is stored for each user
    /**
     * @Route("/storage/sample/{parentSampleId}/user_clone", name="set_users_cloned_sample")
     * @Method("POST")
     * @Security("has_role('ROLE_USER')")
     *
     * @return Response
     */
    public function setClonedSample($parentSampleId)
    {

        $user = $this->getUser();
        $em = $this->getEntityManager();
        $sampleRepo = $em->getRepository('AppBundle\Entity\Storage\Sample');
        $parentSample = $sampleRepo->find($parentSampleId);
        $user->setClonedSample($parentSample);

        $em->flush();

        return $this->getJsonResponse(json_encode(array('success' => 'success')));
    }

    // VIOLATION -- This should really not be in common -- we should have a second table with more data
    // VIOLATION -- THIS SHOULD PROBABLY BE ON USER ALSO
    /**
     * @Route("/storage/sample/user_clone", name="get_users_cloned_sample")
     * @Method("GET")
     * @Security("has_role('ROLE_USER')")
     *
     * @return Response
     */
    public function getClonedSample()
    {
        $user = $this->getUser();
        $data =$this->getSerializationHelper()->serialize(array('data'=>$user->getClonedSample()));
        return $this->getJsonResponse($data);
    }




}

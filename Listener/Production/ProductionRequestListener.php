<?php

namespace Carbon\ApiBundle\Listener\Production;

use Carbon\ApiBundle\Entity\Production\BaseRequest;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\UnitOfWork;
use Symfony\Bridge\Monolog\Logger;

class ProductionRequestListener
{
    /**
     * inserted requests to make sure we don't have the same alias for two requests of
     * the same type being submitted in one request
     */
    public $insertedRequests = array();

    /**
     * Turn the listener on or off
     */
    protected $enabled = true;


    public function onFlush(OnFlushEventArgs $args)
    {

        if (!$this->enabled) {
            return;
        }


        $em = $args->getEntityManager();
        $uow = $em->getUnitOfWork();

        foreach ($uow->getScheduledEntityInsertions() as $keyEntity => $entity) {

            $this->updateAlias($em, $uow, $entity);

        }

        foreach ($uow->getScheduledEntityUpdates() as $keyEntity => $entity) {

            // must be instance of  base request and must be part of a pipeline
            if ($entity instanceof BaseRequest && $entity->getPipeline()) {

                $changeSet = $uow->getEntityChangeSet($entity);

                // return if status unchanged
                if (array_key_exists('status', $changeSet) === FALSE) {
                    return;
                }

                // return if status not changed to completed
                if ($changeSet['status'][1] != BaseRequest::STATUS_COMPLETED) {
                    return;
                }

                // now find the requests we are leading into
                $outputRequests = $this->findOutputRequests($em, $entity);

                // return if this is request is a dead end
                if (count($outputRequests) === 0) {
                    return;
                }

                foreach ($outputRequests as $outputRequest) {

                    // return if the output request is still waiting for more requests
                    if (!$this->allInputRequestsCompleted($em, $outputRequest)) {
                        return;
                    }

                    // if we make it here then all input requests are completed for this output request
                    // so we can start the request and link all the input samples
                    $inputSamples = $this->getInputSamples($em, $outputRequest);

                    $metaRequest = $em->getClassMetadata(get_class($outputRequest));

                    foreach ($metaRequest->associationMappings as $associationMapping) {
                        if ($associationMapping['fieldName'] === 'inputSamples') {
                            $targetEntity = $associationMapping['targetEntity'];
                        }
                    }
                    foreach ($inputSamples as $inputSample) {

                        $outputSample = new $targetEntity();
                        $outputSample->setRequest($outputRequest);
                        $outputSample->setSample($inputSample);
                        $uow->persist($outputSample);
                        $metaOutputSample = $em->getClassMetadata(get_class($outputSample));
                        $uow->computeChangeSet($metaOutputSample, $outputSample);
                    }

                    $outputRequest->setStatus(BaseRequest::STATUS_PENDING);
                    $this->updateAlias($em, $uow, $outputRequest);
                    $metaRequest = $em->getClassMetadata(get_class($outputRequest));
                    $uow->computeChangeSet($metaRequest, $outputRequest);

                }

            }

        }

        // reset
        $this->insertedRequests = [];

    }

    private function updateAlias($em, $uow, $entity)
    {
        if ($entity instanceof BaseRequest && $entity->getStatus() != BaseRequest::STATUS_PENDING_PIPELINE) {

            $qb = $em->createQueryBuilder();

            $startOfMonth = new \DateTime($entity->getCreatedAt()->format('Y-m-01'));
            $endOfMonth = new \DateTime($entity->getCreatedAt()->format('Y-m-t'));
            $endOfMonth->setTime(23, 59, 59);

            $total = $qb
                ->select('count(d.id)')
                ->from(get_class($entity),'d')
                ->add('where', $qb->expr()->andX(
                    $qb->expr()->between(
                        'd.createdAt',
                        ':from',
                        ':to'
                    ),
                    $qb->expr()->notIn(
                        'd.status',
                        array(
                            BaseRequest::STATUS_PENDING_PIPELINE,
                        )
                    )
                ))
                ->setParameters(array(
                    'from' => $startOfMonth,
                    'to' => $endOfMonth,
                ))
                ->getQuery()
                ->getSingleScalarResult()
            ;

            $total = $total + 1;

            if (array_key_exists(get_class($entity), $this->insertedRequests)) {
                $total = $total + $this->insertedRequests[get_class($entity)];
            }

            $alias = sprintf('%s%s-%s', $entity->getAliasPrefix(), $startOfMonth->format('my'), $total);

            $metaDna = $em->getClassMetadata(get_class($entity));

            $entity->setAlias($alias);

            if (array_key_exists(get_class($entity), $this->insertedRequests)) {
                $this->insertedRequests[get_class($entity)] = $this->insertedRequests[get_class($entity)] + 1;
            } else {
                $this->insertedRequests[get_class($entity)] = 1;
            }

            $uow->recomputeSingleEntityChangeSet($metaDna, $entity);

        }
    }

    private function getInputSamples($em, BaseRequest $productionRequest)
    {
        $pipelineRequest = $em->getRepository('AppBundle\Entity\Production\PipelineRequest')->findOneBy(array(
            'entity' => get_class($productionRequest)
        ));

        $inputRequests = $em
            ->getRepository('AppBundle\Entity\Production\PipelineInputRequest')
            ->findBy(array(
                'toPipelineRequest' => $pipelineRequest,
                'toRequestId' => $productionRequest->getId()
            ))
        ;

        $samples = array();

        foreach ($inputRequests as $inputRequest) {

            $req = $em->getRepository($inputRequest->getFromPipelineRequest()->getEntity())->find($inputRequest->getFromRequestId());

            foreach ($req->getOutputSamples() as $outputSample) {
                $samples[] = $outputSample->getSample();
            }

        }

        return $samples;
    }

    private function findOutputRequests($em, BaseRequest $productionRequest)
    {
        $outputRequests = array();
        $pipelineRequest = $em->getRepository('AppBundle\Entity\Production\PipelineRequest')->findOneBy(array(
            'entity' => get_class($productionRequest)
        ));

        $inputRequests = $em
            ->getRepository('AppBundle\Entity\Production\PipelineInputRequest')
            ->findBy(array(
                'fromPipelineRequest' => $pipelineRequest,
                'fromRequestId' => $productionRequest->getId()
            ))
        ;

        foreach ($inputRequests as $inputRequest) {

            $outputRequests[] = $em->getRepository($inputRequest->getToPipelineRequest()->getEntity())->find($inputRequest->getToRequestId());

        }

        return $outputRequests;
    }

    private function allInputRequestsCompleted($em, BaseRequest $productionRequest)
    {
        $pipelineRequest = $em->getRepository('AppBundle\Entity\Production\PipelineRequest')->findOneBy(array(
            'entity' => get_class($productionRequest)
        ));

        $inputRequests = $em
            ->getRepository('AppBundle\Entity\Production\PipelineInputRequest')
            ->findBy(array(
                'toPipelineRequest' => $pipelineRequest,
                'toRequestId' => $productionRequest->getId()
            ))
        ;

        foreach ($inputRequests as $inputRequest) {

            $request = $em->getRepository($inputRequest->getFromPipelineRequest()->getEntity())->find($inputRequest->getFromRequestId());
            if ($request->getStatus() !== BaseRequest::STATUS_COMPLETED) {
                return false;
            }

        }

        return true;
    }
}

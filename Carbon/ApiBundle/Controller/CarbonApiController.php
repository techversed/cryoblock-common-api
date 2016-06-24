<?php

namespace Carbon\ApiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Abstract class to be extended by all api resources
 *
 * @author Andre Jon Branchizio <andrejbranch@gmail.com>
 * @version 1.01
 */
abstract class CarbonApiController extends Controller
{
    /**
     * Handles the HTTP GET for any resource/entity
     *
     * @return Symfony\Component\HttpFoundation\Response response with serialized resources
     */
    protected function handleGet()
    {
        $entityRepository = $this->getEntityRepository();

        $request = $this->getRequest();

        $isDataTableRequest = $this->isDataTableRequest($request);

        $data = $this->getSerializationHelper()->serialize(
            $this->getGrid($isDataTableRequest)->getResult($this->getEntityRepository())
        );

        return $this->getJsonResponse($data);
    }

    /**
     * Default post handling for resource creation
     *
     * @return Symfony\Component\HttpFoundation\Response
     */
    protected function handlePost()
    {
        $request = $this->getRequest();

        if (($contentType = $request->getContentType()) !== 'json') {
            return new Response(sprintf(
                'Content type must be json, %s given',
                $contentType
            ), 415);
        }

        $entityClass = $this->getEntityClass();
        $entity = new $entityClass();

        $formBuilder = $this->createFormBuilder($entity, array(
            'csrf_protection' => false,
        ));

        $columnNames = $this->getAnnotationReader()->getEntityColumnNames($entityClass);

        foreach ($columnNames as $columnName) {
            $formBuilder->add($columnName);
        }

        $form = $formBuilder->getForm();

        $form->bind(json_decode($request->getContent(), true));

        if (!$form->isValid()) {
            return new Response($form->getErrorsAsString(), 401);
        }

        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();

        return $this->getJsonResponse($this->getSerializationHelper()->serialize($entity));
    }

    /**
     * Default PUT handling for resource update
     *
     * @return Symfony\Component\HttpFoundation\Response
     */
    protected function handlePut()
    {
        $request = $this->getRequest();

        if (($contentType = $request->getContentType()) !== 'json') {
            return new Response(sprintf(
                'Content type must be json, %s given',
                $contentType
            ), 415);
        }

        $gridResult = $this->getGrid()->getResult($this->getEntityRepository());

        if (($gridResultCount = count($gridResult['data'])) > 1 || $gridResultCount === 0) {
            return new Response(sprintf(
                'Expected 1 filtered resource to update but found %s',
                $gridResultCount
            ), 404);
        }

        $entity = $gridResult['data'][0];

        $formBuilder = $this->createFormBuilder($entity, array(
            'csrf_protection' => false,
        ));

        $columnNames = $this->getAnnotationReader()->getEntityColumnNames($this->getEntityClass());

        foreach ($columnNames as $columnName) {
            $formBuilder->add($columnName);
        }

        $form = $formBuilder->getForm();

        $form->submit(json_decode($request->getContent(), true), false);

        if (!$form->isValid()) {
            return new Response($form->getErrorsAsString(), 401);
        }

        $this->getEntityManager()->flush();

        return $this->getJsonResponse($this->getSerializationHelper()->serialize($entity));
    }

    /**
     * Default DELETE handling for resource update
     *
     * @return Symfony\Component\HttpFoundation\Response
     */
    protected function handleDelete()
    {
        $gridResult = $this->getGrid()->getResult($this->getEntityRepository());

        if (($foundResultsCount = count($gridResult['data'])) > 1 || $foundResultsCount === 0) {
            return new Response(sprintf(
                'Delete method expects one entity to be found for deletion, %s found from GET params',
                $foundResultsCount
            ), 401);
        }

        $entity = $gridResult['data'][0];

        $this->getEntityManager()->remove($entity);
        $this->getEntityManager()->flush();

        return $this->getJsonResponse(json_encode(array('success' => true)));
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
     * Get the entity class defined in controller constant
     *
     * @throws \LogicException
     * @return string
     */
    protected function getEntityClass()
    {
        if (!defined('static::RESOURCE_ENTITY')) {
            throw new \LogicException('No resource entity is defined. Did you add the RESOURCE_ENTITY const to your resource controller?');
        }

        return static::RESOURCE_ENTITY;
    }

    /**
     * Get the repository class for the given resource/entity
     *
     * @return Doctrine\ORM\EntityRepository
     */
    protected function getEntityRepository()
    {
        return $this->getEntityManager()->getRepository($this->getEntityClass());
    }

    /**
     * Get the serialization helper
     *
     * @return Carbon\ApiBundle\Service\Serialization\Helper
     */
    protected function getSerializationHelper()
    {
        return $this->get('carbon_api.serialization_helper');
    }

    /**
     * Get Carbon Grid
     *
     * @param  boolean $useDataTableGrid
     * @return Carbon\ApiBundle\Grid\CarbonGrid
     */
    protected function getGrid($useDataTableGrid = false)
    {
        if ($useDataTableGrid) {

            return $this->get('carbon_api.data_table_grid');

        }

        return $this->get('carbon_api.grid');
    }

    /**
     * Get Carbon Annotation Reader
     *
     * @return Carbon\ApiBundle\Service\CarbonAnnotationReader
     */
    protected function getAnnotationReader()
    {
        return $this->get('carbon_api.annotation_reader');
    }

    /**
     * Check the request params to determine if this request is
     * from jquery data tables
     *
     * @param  Request $request
     * @return boolean
     */
    protected function isDataTableRequest(Request $request)
    {
        return NULL !== $request->get('draw');
    }

    /**
     * Return a json response with content of provided json data
     *
     * @param  string $data json string
     * @return Symfony\Component\HttpFoundation\Response
     */
    protected function getJsonResponse($data, $status = 200)
    {
        $request = $this->getRequest();
        $sentHeaders = array_keys($request->headers->all());

        if (!in_array('apikey', $sentHeaders)) {
            $sentHeaders[] = 'apikey';
        }

        if (!in_array('Content-Type', $sentHeaders)) {
            $sentHeaders[] = 'Content-Type';
        }

        if (!in_array('X-CARBON-SERIALIZATION-GROUPS', $sentHeaders)) {
            $sentHeaders[] = 'X-CARBON-SERIALIZATION-GROUPS';
        }

        return new Response($data, $status, array(
            'Content-Type' => 'application/json',
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Headers' => implode($sentHeaders, ','),
            'Access-Control-Allow-Methods' => $request->headers->get('Access-Control-Request-Method'),
        ));
    }
}

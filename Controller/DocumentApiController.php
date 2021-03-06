<?php

namespace Carbon\ApiBundle\Controller;

use Doctrine\ORM\Query\Expr\Join;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\Form;

/**
 * Abstract class to be extended by all api resources
 *
 * @author Andre Jon Branchizio <andrejbranch@gmail.com>
 * @version 1.01
 */
abstract class DocumentApiController  extends Controller
{
    /**
     * Handles the HTTP GET for any resource/entity
     *
     * @return Symfony\Component\HttpFoundation\Response response with serialized resources
     */
    protected function handleGet()
    {
        $documentReposistory = $this->getDocumentRepository();

        $request = $this->getRequest();

        $data = $this->getSerializationHelper()->serialize(
            $this->getGrid()->getResult($this->getDocumentRepository())
        );

        return $this->getJsonResponse($data);
    }

    /**
     * Handle the HTTP GET for a many to many linker object resource
     *
     * @param  $type string
     * @param  $id   int
     *
     * @return Symfony\Component\HttpFoundation\Response response with serialized objects
     */
    protected function handleMTMGet($type, $id)
    {
        if (!defined('static::RESOURCE_ENTITY')) {
            throw new \LogicException('No resource entity is defined. Did you add the RESOURCE_ENTITY const to your resource controller?');
        }

        if (isset($this->resourceLinkMap) === FALSE) {
            throw new \LogicException("Property resourceLinkMap must be defined for many to many get");
        }

        $selectable = (bool) $this->getRequest()->get('cSelectable');

        $map = $this->resourceLinkMap[$type];

        $qb = $this->getEntityManager()->createQueryBuilder()   ;
        $sub = $this->getEntityManager()->createQueryBuilder();

        $alias = 'a';
        $subAlias = 'b';

        if ($selectable) {

            $qb->select(array($alias))->from($map['returnedEntity'], $alias);

            $sub->select($subAlias);
            $sub->from(static::RESOURCE_ENTITY, $subAlias);
            $sub->andWhere(sprintf('%s.%s = %s', $subAlias, $map['whereColumn'], $id));
            $sub->andWhere(sprintf('%s.%s = %s.id', $subAlias, $map['joinColumn'], $alias));

            $qb->andWhere($qb->expr()->not($qb->expr()->exists($sub->getDQL())));


        } else {

            $qb->select(array($alias))
                ->from($map['returnedEntity'], $alias)
                ->innerJoin(static::RESOURCE_ENTITY, $subAlias, Join::WITH, sprintf('%s.%s = %s.id', $subAlias, $map['joinColumn'], $alias))
                ->where(sprintf('%s.%s = :whereId', $subAlias, $map['whereColumn']))
                ->setParameter('whereId', $id)
            ;

        }

        $results = $this->getGrid()->handleQueryFilters($qb, $alias, $map['returnedEntity']);

        $serialized = $this->getSerializationHelper()->serialize($results);

        return $this->getJsonResponse($serialized);

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

        if (!defined('static::FORM_TYPE')) {
            throw new \LogicException('No form type specified. Did you add the FORM_TYPE const to your resource controller?');
        }

        $form = $this->createForm(static::FORM_TYPE, $entity);

        $form->submit(json_decode($request->getContent(), true));

        if (!$form->isValid()) {

            return $this->getFormErrorResponse($form);

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

        if (!defined('static::FORM_TYPE')) {
            throw new \LogicException('No form type specified. Did you add the FORM_TYPE const to your resource controller?');
        }

        $form = $this->createForm(static::FORM_TYPE, $entity);
        $form->submit(json_decode($request->getContent(), true));

        if (!$form->isValid()) {

            return $this->getFormErrorResponse($form);

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

        if ($this->resourceSecurity) {
            $resourceSecurity = $this->resourceSecurity;
            $this->denyAccessUnlessGranted('delete', array($entity, $resourceSecurity));
        }

        $this->getEntityManager()->remove($entity);
        $this->getEntityManager()->flush();

        return $this->getJsonResponse(json_encode(array('success' => true)));
    }

    /**
     * Get the doctrine document manager
     *
     * @return DDoctrine\ODM\MongoDB\DocumentManager
     */
    protected function getDocumentManager()
    {
        return $this->get('doctrine_mongodb.odm.default_document_manager');
    }

    /**
     * Get the entity class defined in controller constant
     *
     * @throws \LogicException
     * @return string
     */
    protected function getDocumentClass()
    {
        if (!defined('static::RESOURCE_DOCUMENT')) {
            throw new \LogicException('No resource entity is defined. Did you add the RESOURCE_DOCUMENT const to your resource controller?');
        }

        return static::RESOURCE_DOCUMENT;
    }

    /**
     * Get the repository class for the given resource/entity
     *
     * @return Doctrine\ORM\EntityRepository
     */
    protected function getDocumentRepository()
    {
        return $this->getDocumentManager()->getRepository($this->getDocumentClass());
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

        return new Response($data, $status, array(
            'Content-Type' => 'application/json',
        ));
    }

    protected function getFormErrorResponse(Form $form)
    {
        $formErrors = $form->getErrors(true);
        $errors = array();
        foreach ($formErrors as $error) {

            $name = $error->getOrigin()->getName();
            $message = $error->getMessage();

            if (!isset($errors[$name])) {
                $errors[$name] = array();
            }

            $errors[$name][] = $message;

        }

        return $this->getJsonResponse($this->getSerializationHelper()->serialize($errors), 400);
    }
}

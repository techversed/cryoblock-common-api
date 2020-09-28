<?php

namespace Carbon\ApiBundle\Controller;

use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\ORM\Query\Expr\Join;
use JMS\Serializer\Naming\CamelCaseNamingStrategy;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Form\Form;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;

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
        $this->checkPermission('GET');

        $entityRepository = $this->getEntityRepository();

        $request = $this->getRequest();

        $isDataTableRequest = $this->isDataTableRequest($request);

        $data = $this->getSerializationHelper()->serialize(
            $this->getGrid($isDataTableRequest)->getResult($this->getEntityRepository())
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
    protected function handleMTMGetWithMetaData($type, $id)
    {
        $this->checkPermission('GET');

        if (!defined('static::RESOURCE_ENTITY')) {
            throw new \LogicException('No resource entity is defined. Did you add the RESOURCE_ENTITY const to your resource controller?');
        }

        if (isset($this->resourceLinkMap) === FALSE) {
            throw new \LogicException("Property resourceLinkMap must be defined for many to many get");
        }

        $selectable = (bool) $this->getRequest()->get('cSelectable');

        $map = $this->resourceLinkMap[$type];

        $qb = $this->getEntityManager()->createQueryBuilder();
        $sub = $this->getEntityManager()->createQueryBuilder();

        $alias = 'a';
        $subAlias = 'b';

        if ($selectable) {

            echo "This is not implemented yet -- handleMTMGetWithMetaData"; // It would be a really good idea to finish this up at some point -- in order to get this release out it is not an option to finish it now
            $qb->select(array($alias))->from($map['returnedEntity'], $alias);

            $sub->select($subAlias);
            $sub->from(static::RESOURCE_ENTITY, $subAlias);
            $sub->andWhere(sprintf('%s.%s = %s', $subAlias, $map['whereColumn'], $id));
            $sub->andWhere(sprintf('%s.%s = %s.id', $subAlias, $map['joinColumn'], $alias));

            $qb->andWhere($qb->expr()->not($qb->expr()->exists($sub->getDQL())));


        } else {

            // echo "test";
            $qb->select(array($subAlias))
                ->from(static::RESOURCE_ENTITY, $subAlias)
                ->innerJoin($map['returnedEntity'], $alias, Join::WITH, sprintf('%s.%s = %s.id', $subAlias, $map['joinColumn'], $alias))
                ->where(sprintf('%s.%s = :whereId', $subAlias, $map['whereColumn']))
                ->setParameter('whereId', $id)
            ;
            // $qb->select(array($alias))
            //     ->from($map['returnedEntity'], $alias)
            //     ->innerJoin(static::RESOURCE_ENTITY, $subAlias, Join::WITH, sprintf('%s.%s = %s.id', $subAlias, $map['joinColumn'], $alias))
            //     ->where(sprintf('%s.%s = :whereId', $subAlias, $map['whereColumn']))
            //     ->setParameter('whereId', $id)
            // ;

        }

        $results = $this->getGrid()->handleQueryFilters($qb, $alias, $map['returnedEntity']);

        $serialized = $this->getSerializationHelper()->serialize($results);

        return $this->getJsonResponse($serialized);
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
        $this->checkPermission('GET');

        if (!defined('static::RESOURCE_ENTITY')) {
            throw new \LogicException('No resource entity is defined. Did you add the RESOURCE_ENTITY const to your resource controller?');
        }

        if (isset($this->resourceLinkMap) === FALSE) {
            throw new \LogicException("Property resourceLinkMap must be defined for many to many get");
        }

        $selectable = (bool) $this->getRequest()->get('cSelectable');

        $map = $this->resourceLinkMap[$type];

        $qb = $this->getEntityManager()->createQueryBuilder();
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
        $this->checkPermission('POST');

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

        $this->checkPermission('PUT', $entity);

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
     * Default DELETE handling for resource
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

        $this->checkPermission('DELETE', $entity);

        $metadata = $this->getEntityManager()->getClassMetaData(get_class($entity));

        foreach ($metadata->associationMappings as $associationMapping) {
            if (!$associationMapping['isCascadeRemove'] && $associationMapping['type'] != ClassMetadataInfo::MANY_TO_ONE) {
                $undeletableRelationsExist = $this->getEntityManager()->getRepository($associationMapping['targetEntity'])->findOneBy(array(
                    $associationMapping['mappedBy'] => $entity
                ));
                if ($undeletableRelationsExist) {
                    $relationName = preg_replace('/[A-Z]/', ' ' . '\\0', $associationMapping['fieldName']);
                    $relationName = ucfirst($relationName);
                    $message = 'The object your trying to delete has links to "' . $relationName . '" that can not be deleted.';
                    $headers = array('CB-DELETE-MESSAGE' => $message);
                    throw new HttpException(403, $message, null, $headers);
                }
            }
        }

        // we can delete the entity, lets soft delete any deletable relations
        foreach ($metadata->associationMappings as $associationMapping) {
            if ($associationMapping['isCascadeRemove'] && $associationMapping['type'] != ClassMetadataInfo::MANY_TO_MANY) {
                $now = new \DateTime();
                $q = $this->getEntityManager()->createQuery(sprintf(
                    'UPDATE %s e SET e.deletedAt = \'%s\' WHERE e.%s = %s',
                    $associationMapping['targetEntity'],
                    $now->format('Y-m-d H:i:s'),
                    $associationMapping['mappedBy'],
                    $entity->getId()
                ));
                $q->execute();
            }
        }

        $this->getEntityManager()->remove($entity);
        $this->getEntityManager()->flush();

        return $this->getJsonResponse(json_encode(array('success' => true)));
    }

    /**
     * Default PURGE handling for resource
     *
     * @return Symfony\Component\HttpFoundation\Response
     */
    protected function handlePurge()
    {
        $filter = $this->getEntityManager()->getFilters()->enable('softdeleteable');
        $filter->disableForEntity($this->getEntityClass());

        $gridResult = $this->getGrid()->getResult($this->getEntityRepository());

        if (($foundResultsCount = count($gridResult['data'])) > 1 || $foundResultsCount === 0) {
            return new Response(sprintf(
                'Delete method expects one entity to be found for deletion, %s found from GET params',
                $foundResultsCount
            ), 401);
        }

        $entity = $gridResult['data'][0];

        $this->checkPermission('DELETE', $entity);

        $metadata = $this->getEntityManager()->getClassMetaData(get_class($entity));

        // we can delete the entity, lets soft delete any deletable relations
        foreach ($metadata->associationMappings as $associationMapping) {
            if ($associationMapping['isCascadeRemove'] && $associationMapping['type'] != ClassMetadataInfo::MANY_TO_MANY) {
                $filter->disableForEntity($associationMapping['targetEntity']);
                $now = new \DateTime();
                $q = $this->getEntityManager()->createQuery(sprintf(
                    'DELETE FROM %s e WHERE e.%s = %s',
                    $associationMapping['targetEntity'],
                    $associationMapping['mappedBy'],
                    $entity->getId()
                ));
                $q->execute();
            }
        }

        $this->getEntityManager()->remove($entity);
        $this->getEntityManager()->flush();

        return $this->getJsonResponse(json_encode(array('success' => true)));
    }

    /**
     * Default PATCH handling used for restoring an object
     *
     * @return Symfony\Component\HttpFoundation\Response
     */
    protected function handlePatch()
    {
        $filter = $this->getEntityManager()->getFilters()->enable('softdeleteable');
        $filter->disableForEntity($this->getEntityClass());

        $gridResult = $this->getGrid()->getResult($this->getEntityRepository());

        if (($foundResultsCount = count($gridResult['data'])) > 1 || $foundResultsCount === 0) {
            return new Response(sprintf(
                'Delete method expects one entity to be found for deletion, %s found from GET params',
                $foundResultsCount
            ), 401);
        }

        $entity = $gridResult['data'][0];

        $this->checkPermission('DELETE', $entity);

        $metadata = $this->getEntityManager()->getClassMetaData(get_class($entity));

        // we can delete the entity, lets soft delete any deletable relations
        foreach ($metadata->associationMappings as $associationMapping) {
            if ($associationMapping['isCascadeRemove'] && $associationMapping['type'] != ClassMetadataInfo::MANY_TO_MANY) {
                $filter->disableForEntity($associationMapping['targetEntity']);
                $now = new \DateTime();
                $q = $this->getEntityManager()->createQuery(sprintf(
                    'UPDATE %s e SET e.deletedAt = NULL WHERE e.%s = %s',
                    $associationMapping['targetEntity'],
                    $associationMapping['mappedBy'],
                    $entity->getId()
                ));
                $q->execute();
            }
        }

        $entity->setDeletedAt(null);
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

        return $this->getJsonResponse($this->getSerializationHelper()->serialize(array('violations' => $errors)), 400);
    }

    protected function checkPermission($method, $entity = null)
    {
        if (isset($this->security) && array_key_exists($method, $this->security)) {

            $allowedRoles = $this->security[$method]['roles'];
            $hasPermission = false;
            $user = $this->getUser();
            foreach ($allowedRoles as $allowedRole) {
                if ($user->hasRole($allowedRole)) {
                    $hasPermission = true;
                }
            }
            $allowedRolesString = implode(', ', $allowedRoles);
            $message = "Action only allowed for users with role(s) " . $allowedRolesString;

            // allow creator of object to perform action
            if (array_key_exists('allow_creator', $this->security[$method]) && $entity) {
                if (method_exists($entity, 'getCreatedBy') && ($entity->getCreatedBy()->getId() == $this->getUser()->getId())) {
                    $hasPermission = true;
                }
                $message .= ' or user ' . $entity->getCreatedBy()->getStringLabel();
            }

            $message .='.';

            if (!$hasPermission) {
                throw new UnauthorizedHttpException($message);
            }
        }
    }
}

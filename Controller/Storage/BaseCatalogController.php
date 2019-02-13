<?php

namespace Carbon\ApiBundle\Controller\Storage;

use Carbon\ApiBundle\Controller\CarbonApiController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Response;

class BaseCatalogController extends CarbonApiController
{
    /**
     * @var string The namespace of the resource entity
     */
    const RESOURCE_ENTITY = "AppBundle\Entity\Storage\Catalog";

    /**
     * @var string The form type for this resource
     */
    const FORM_TYPE = "catalog";

    /**
     * Security config
     */
    protected $security = array(
        'GET' => array(
            'roles' => array('ROLE_USER'),
        ),
        'POST' => array(
            'roles' => array('ROLE_USER'),
        ),
        'PUT' => array(
            'roles' => array('ROLE_INVENTORY_ADMIN'),
            'allow_creator' => true
        ),
        'DELETE' => array(
            'roles' => array('ROLE_INVENTORY_ADMIN'),
        ),
        'PATCH' => array(
            'roles' => array('ROLE_INVENTORY_ADMIN'),
        )
    );

    /**
     * Handles the HTTP get request for the division entity
     *
     * @Route("/storage/catalog", name="catalog_get")
     * @Method("GET")
     * @return [type] [description]
     */
    public function handleGet()
    {
        return parent::handleGet();
    }

    /**
     * Handles the HTTP get request for the card entity
     *
     * @Route("/storage/catalog", name="catalog_post")
     * @Method("POST")
     * @return [type] [description]
     */
    public function handlePost()
    {
        return parent::handlePost();
    }

    /**
     * Handles the HTTP PUT request for the card entity
     *
     * @todo  figure out why PUT method has no request params
     * @Route("/storage/catalog", name="catalog_put")
     * @Method("PUT")
     * @return [type] [description]
     */
    public function handlePut()
    {
        return parent::handlePut();
    }

    /**
     * Handles the HTTP DELETE request for the card entity
     *
     * @Route("/storage/catalog", name="catalog_delete")
     * @Method("DELETE")
     * @return [type] [description]
     */
    public function handleDelete()
    {
        return parent::handleDelete();
    }

    /**
     * @Route("/storage/catalog", name="catalog_patch")
     * @Method("PATCH")
     *
     * @return Response
     */
    public function handlePatch()
    {
        return parent::handlePatch();
    }
}

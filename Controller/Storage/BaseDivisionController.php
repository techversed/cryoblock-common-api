<?php

namespace Carbon\ApiBundle\Controller\Storage;

use AppBundle\Entity\Storage\Division;
use Carbon\ApiBundle\Controller\CarbonApiController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Response;

class BaseDivisionController extends CarbonApiController
{
    /**
     * @var string The namespace of the resource entity
     */
    const RESOURCE_ENTITY = "AppBundle\Entity\Storage\Division";

    /**
     * @var string The form type for this resource
     */
    const FORM_TYPE = "division";

    /**
     * Handles the HTTP get request for the division entity
     *
     * @Route("/storage/division", name="division_get")
     * @Method("GET")
     *
     * @return Response
     */
    public function handleGet()
    {
        return parent::handleGet();
    }

    /**
     * Handles the HTTP get request for the division entity
     *
     * @Route("/storage/division-tree", name="division_tree_get")
     * @Method("GET")
     *
     * @return Response
     */
    public function handleGetTree()
    {
        $nodes = $this->getEntityRepository()->getRootNodesQuery()->getResult();

        $childNodes = $this->getEntityRepository()->getChildrenQuery($nodes[0], true)->getResult();
        $children = json_decode($this->getSerializationHelper()->serialize($childNodes));

        $tree = $this->getSerializationHelper()->serialize($nodes);
        $tree = json_decode($tree, true);
        $tree[0]['__children'] = $children;
        $tree = json_encode($tree);

        return $this->getJsonResponse($tree);

    }

    /**
     * Handles the HTTP get request for getting a divisions children
     *
     * @Route("/storage/division-children/{parentId}", name="division_children_get")
     * @Method("GET")
     *
     * @return Response
     */
    public function handleGetChildren($parentId)
    {
        $node = $this->getEntityRepository()->find($parentId);

        $childNodes = $this->getEntityRepository()->getChildrenQuery($node, true)->getResult();
        $children = $this->getSerializationHelper()->serialize($childNodes);

        return $this->getJsonResponse($children);

    }
    /**
     * Handles the HTTP get request for the card entity
     *
     * @Route("/storage/division", name="division_post")
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
     * @Route("/storage/division", name="division_put")
     * @Method("PUT")
     *
     * @return Response
     */
    public function handlePut()
    {
        $response = parent::handlePut();

        return $response;
    }

    /**
     * Handles the HTTP DELETE request for the card entity
     *
     * @Route("/storage/division", name="division_delete")
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
     * @Route("/storage/division/{id}/move", name="division_move")
     * @Method("POST")
     *
     * @return Response
     */
    public function move($id)
    {
        $request = $this->getRequest();
        $content = $request->getContent();
        $content = (json_decode($content, true));
        $repo = $this->getEntityRepository();
        $division = $repo->find($id);

        if (isset($content['firstChildOf'])) {
            $parent = $repo->find($content['firstChildOf']);
            $repo->persistAsFirstChildOf($division, $parent);
        }

        if (isset($content['lastChildOf'])) {
            $parent = $repo->find($content['lastChildOf']);
            $repo->persistAsLastChildOf($division, $parent);
        }

        if (isset($content['nextSiblingOf'])) {
            $sibling = $repo->find($content['nextSiblingOf']);
            $repo->persistAsNextSiblingOf($division, $sibling);
        }

        if (isset($content['previousSiblingOf'])) {
            $sibling = $repo->find($content['previousSiblingOf']);
            $repo->persistAsPrevSiblingOf($division, $sibling);
        }

        $this->getEntityManager()->flush();

        return $this->getJsonResponse(json_encode(array('success' => 'success')));
    }

    /**
     * Handles the HTTP POST request for moving a division
     *
     * @Route("/storage/division/match/{sampleTypeId}/{storageContainerId}", name="division_match")
     * @Method("GET")
     *
     * @return Response
     */
    public function match($sampleTypeId, $storageContainerId)
    {
        $repo = $this->getEntityRepository();
        $qb = $repo->buildMatchQuery($sampleTypeId, $storageContainerId, $this->getUser());

        $results = $this->getGrid()->handleQueryFilters($qb, 'd', static::RESOURCE_ENTITY);

        $serialized = $this->getSerializationHelper()->serialize($results);

        return $this->getJsonResponse($serialized);
    }

    /**
     * Handles the HTTP POST request for moving a division
     *
     * @Route("/storage/division/{id}/available-cells", name="division_available_cells")
     * @Method("GET")
     *
     * @return Response
     */
    public function availableCells($id)
    {
        $repo = $this->getEntityRepository();
        $division = $repo->find($id);

        $availableCells = $repo->getAvailableCells($division);

        $serialized = $this->getSerializationHelper()->serialize($availableCells);

        return $this->getJsonResponse($serialized);
    }

}
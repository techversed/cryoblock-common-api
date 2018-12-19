<?php

namespace Carbon\ApiBundle\Controller\Help;

use AppBundle\Entity\Help;
use Carbon\ApiBundle\Serializer\Dot;
use Carbon\ApiBundle\Controller\CarbonApiController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Doctrine\Common\Collections\ArrayCollection;

class BaseHelpController extends CarbonApiController
{
    /**
     * @var string The namespace of the resource entity
     */
    const RESOURCE_ENTITY = "AppBundle\Entity\Help\Help";

    /**
     * @var string The form type for this resource
     */
    const FORM_TYPE = "help";

    /**
     * Handles the HTTP get request for the help entity
     *
     * @Route("/help/help", name="help_get")
     * @Method("GET")
     *
     * @return Response
     */
    public function handleGet()
    {
        return parent::handleGet();
    }

    /**
     * Handles the HTTP get request for the help entity
     *
     * @Route("/help/help-tree", name="help_tree_get")
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
     * Handles the HTTP get request for getting a helps children
     *
     * @Route("/help/help-children/{parentId}", name="help_children_get")
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
     * @Route("/help/help", name="help_post")
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
     * @Route("/help/help", name="help_put")
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

        $help = $gridResult['data'][0];

        // $canEdit = $this->getEntityManager()->getRepository('AppBundle\Entity\Help\Help')
        //     ->canUserEdit($help, $this->getUser())
        // ;

        // if (!$canEdit) {
        //     return $this->getJsonResponse($this->getSerializationHelper()->serialize(
        //         array('violations' => array(array(
        //             'Sorry, you do not have permission to edit help ' . $help->getId(),
        //         )))
        //     ), 400);
        // }

        return parent::handlePut();
    }

    /**
     * Handles the HTTP DELETE request for the card entity
     *
     * @Route("/help/help", name="help_delete")
     * @Method("DELETE")
     *
     * @return Response
     */
    public function handleDelete()
    {
        $gridResult = $this->getGrid()->getResult($this->getEntityRepository());

        if (($foundResultsCount = count($gridResult['data'])) > 1 || $foundResultsCount === 0) {
            return new Response(sprintf(
                'Delete method expects one entity to be found for deletion, %s found from GET params',
                $foundResultsCount
            ), 401);
        }

        $help = $gridResult['data'][0];

        $canEdit = $this->getEntityRepository()->canUserEdit($help, $this->getUser());

        if (!$canEdit) {
            $message = 'You do not have permission to delete this help.';
            throw new UnauthorizedHttpException($message);
        }

        $response = parent::handleDelete();

        return $response;
    }

    /**
     * @Route("/help/help", name="help_patch")
     * @Method("PATCH")
     *
     * @return Response
     */
    public function handlePatch()
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

        $help = $gridResult['data'][0];

        $canEdit = $this->getEntityRepository()->canUserEdit($help, $this->getUser());

        if (!$canEdit) {
            $message = 'You do not have permission to restore this help.';
            throw new UnauthorizedHttpException($message);
        }

        $response = parent::handlePatch();

        if ($response->getStatusCode() == 200) {

            $this->getEntityRepository()->recover();
            $this->getEntityManager()->flush();

        }


        return $response;
    }

    /**
     * @Route("/help/help", name="help_purge")
     * @Method("PURGE")
     *
     * @return Response
     */
    public function handlePurge()
    {
        return parent::handlePurge();
    }

    /**
     * Handles the HTTP POST request for moving a help
     *
     * @Route("/help/help/{id}/move", name="help_move")
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
        $help = $repo->find($id);

        if (isset($content['firstChildOf'])) {
            $parent = $repo->find($content['firstChildOf']);
            $repo->persistAsFirstChildOf($help, $parent);
        }

        if (isset($content['lastChildOf'])) {
            $parent = $repo->find($content['lastChildOf']);
            $repo->persistAsLastChildOf($help, $parent);
        }

        if (isset($content['nextSiblingOf'])) {
            $sibling = $repo->find($content['nextSiblingOf']);
            $repo->persistAsNextSiblingOf($help, $sibling);
        }

        if (isset($content['previousSiblingOf'])) {
            $sibling = $repo->find($content['previousSiblingOf']);
            $repo->persistAsPrevSiblingOf($help, $sibling);
        }

        $this->getEntityManager()->flush();

        return $this->getJsonResponse(json_encode(array('success' => 'success')));
    }

}

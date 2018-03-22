<?php

namespace Carbon\ApiBundle\Controller\Production;

use Carbon\ApiBundle\Controller\CarbonApiController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Carbon\ApiBundle\Serializer\Dot;

class CatalogNameDecideController extends CarbonApiController
{
    /**
     * @Route("/production/catalog-name-decide", name="production_catalog_name_decide")
     * @Method("POST")
     *
     * @return Response
     */
    public function handlePost()
    {
        $request = $this->getRequest();
        $data = json_decode($request->getContent(), true);

        $request = $this->getEntityManager()->getRepository($data['entity'])->find($data['id']);

        $catalogRepo = $this->getEntityManager()->getRepository('AppBundle\Entity\Storage\Catalog');
        $parentCatalogRepo = $this->getEntityManager()->getRepository('AppBundle\Entity\Storage\ParentCatalog');

        $requestInputSamples = $request->getInputSamples();

        // nothing to do if no input samples
        if (count($requestInputSamples) == 0) {
            return $this->getJsonResponse(json_encode(array(
                'catalogName' => '',
                'totalInputSamples' => 0,
                'totalInputCatalogs' => 0,
                'catalogIds' => array(),
                'hasExistingCatalog' => false,
            )));
        }

        $catalogIds = array();
        $catalogNames = array();

        foreach ($requestInputSamples as $requestInputSample) {
            $catalog = $catalogRepo->findOneByName($requestInputSample->getSample()->getCatalog()->getName());
            if ($catalog) {
                $catalogIds[] = $catalog->getId();
                $catalogNames[] = $catalog->getName();
            }
        }

        $catalogIds = array_unique($catalogIds);
        $catalogNames = array_unique($catalogNames);

        $existingCatalog = $this->findExistingCatalog($catalogIds);

        if ($existingCatalog) {
            $decidedCatalogName = $existingCatalog->getName();
            $hasExistingCatalog = true;
        } else {
            $decidedCatalogName = implode(' ', $catalogNames);
            $hasExistingCatalog = false;
        }

        return $this->getJsonResponse(json_encode(array(
            'catalogName' => $decidedCatalogName,
            'totalInputSamples' => count($requestInputSamples),
            'totalInputCatalogs' => count($catalogIds),
            'catalogIds' => $catalogIds,
            'hasExistingCatalog' => $hasExistingCatalog,
        )));
    }

    private function findExistingCatalog($catalogIds)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();

        $subQueries = array();

        $count = 0;
        foreach ($catalogIds as $catalogId) {
            $alias = 'pc' . $count;
            $subQuery = $this->getEntityManager()->createQueryBuilder();
            $subQuery
                ->select($alias . '.id')
                ->from('AppBundle\\Entity\\Storage\\ParentCatalog', $alias)
                ->where($alias . '.parentCatalogId = c.id')
                ->andWhere($alias . '.childCatalogId = ' . $catalogId)
            ;
            $subQueries[] = 'EXISTS (' . $subQuery->getDQL() . ')';
            $count++;
        }

        $alias = 'pc' . $count;
        $sub3 = $this->getEntityManager()->createQueryBuilder();
        $sub3
            ->select('count(' . $alias . ')')
            ->from('AppBundle\\Entity\\Storage\\ParentCatalog', $alias)
            ->where($alias . '.parentCatalogId = c.id')
        ;

        $subQueries[] = "(" . $sub3->getDQL() . ') = ' . count($catalogIds);

        $qb->select(array('c'))
            ->from('AppBundle\Entity\Storage\Catalog', 'c')
        ;

        foreach ($subQueries as $subQuery) {
            $qb->andWhere($subQuery);
        }

        $result = $qb->getQuery()->getOneOrNullResult();

        return $result;
    }
}

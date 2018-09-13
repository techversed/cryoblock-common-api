<?php

namespace Carbon\ApiBundle\Controller\Storage;

use AppBundle\Entity\Storage\Division;
use Carbon\ApiBundle\Serializer\Dot;
use Carbon\ApiBundle\Controller\CarbonApiController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Doctrine\Common\Collections\ArrayCollection;

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
        $gridResult = $this->getGrid()->getResult($this->getEntityRepository());

        if (($foundResultsCount = count($gridResult['data'])) > 1 || $foundResultsCount === 0) {
            return new Response(sprintf(
                'Delete method expects one entity to be found for deletion, %s found from GET params',
                $foundResultsCount
            ), 401);
        }

        $division = $gridResult['data'][0];

        $canEdit = $this->getEntityManager()->getRepository('AppBundle\Entity\Storage\Division')
            ->canUserEdit($division, $this->getUser())
        ;

        if (!$canEdit) {
            return $this->getJsonResponse($this->getSerializationHelper()->serialize(
                array('violations' => array(array(
                    'Sorry, you do not have permission to edit division ' . $division->getId(),
                )))
            ), 400);
        }

        return parent::handlePut();
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
        $gridResult = $this->getGrid()->getResult($this->getEntityRepository());

        if (($foundResultsCount = count($gridResult['data'])) > 1 || $foundResultsCount === 0) {
            return new Response(sprintf(
                'Delete method expects one entity to be found for deletion, %s found from GET params',
                $foundResultsCount
            ), 401);
        }

        $division = $gridResult['data'][0];

        $canEdit = $this->getEntityRepository()->canUserEdit($division, $this->getUser());

        if (!$canEdit) {
            $message = 'You do not have permission to delete this division.';
            throw new UnauthorizedHttpException($message);
        }

        $response = parent::handleDelete();

        return $response;
    }

    /**
     * @Route("/storage/division", name="division_patch")
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

        $division = $gridResult['data'][0];

        $canEdit = $this->getEntityRepository()->canUserEdit($division, $this->getUser());

        if (!$canEdit) {
            $message = 'You do not have permission to restore this division.';
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
     * @Route("/storage/division", name="division_purge")
     * @Method("PURGE")
     *
     * @return Response
     */
    public function handlePurge()
    {
        return parent::handlePurge();
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

    /**
     * Handles the HTTP POST request for moving a division
     *
     * @Route("/storage/division/{id}/excelDownload", name="excel_download")
     * @Method("GET")
     *
     * @return Response
     */
    public function excelDownload($id){

        //$request = $this->getRequest();
        //$data = json_decode($request->getContent(), true);

        $fileName = 'Request ' . (string) $id . ' Input Samples Template.xlsx';

        $objPHPExcel = new \PHPExcel();

        $prodRequest = $this->getEntityManager()->getRepository('AppBundle\Entity\Storage\Division')->find($id);
        $prodRequestInputSamples = $prodRequest->getSamples();

        $iterator = $prodRequestInputSamples->getIterator();

        $iterator->uasort(function($a,$b){
            return ((ord($a->getDivisionRow()) * 50 + $a->getDivisionColumn() ) < (ord($b->getDivisionRow()) * 50 + $b->getDivisionColumn())) ? -1 : 1;
        });
        $prodRequestInputSamples = new ArrayCollection(iterator_to_array($iterator));

        $prodRequestInputSample = $prodRequestInputSamples[0];

        $importer = $this->container->get('sample.importer');
        $sampleTypeMapping = $importer->getMapping($prodRequestInputSample->getSampleType());
        $sampleTypeMapping = array_merge(array(
            'Id' => array(
                'prop' => 'id',
                'bindTo' => 'id',
                'errorProp' => array('id'),
            )
        ), $sampleTypeMapping);

        $currentSample = 0;

        $aRange = range('A', 'Z');
        $current = 0;
        foreach ($sampleTypeMapping as $label => $column) {

            $objPHPExcel->getActiveSheet()->getColumnDimension($aRange[$current])->setWidth(15);
            $cell = $objPHPExcel->getActiveSheet()->getCell($aRange[$current] . '1');
            $cell->setValue($label);
            $style = $objPHPExcel->getActiveSheet()->getStyle($aRange[$current] . '1');
            $style->getFont()->setBold(true);

            $current++;
        }

        $currentSample = 1;
        $protectedLabels = array(
            'Id',
            'Sample Type',
            'Catalog',
            'Lot',
            'Division',
            'Division Row',
            'Division Column',
        );

        $storageContainers = $this->getEntityManager()->getRepository('AppBundle\\Entity\\Storage\\StorageContainer')->findAll();

        $storageContainerNames = array();
        foreach ($storageContainers as $storageContainer) {
            $storageContainerNames[] = $storageContainer->getName();
        }
        $storageContainerNames = implode(', ', $storageContainerNames);
        $concentrationUnits = implode(', ', array(
            'mg/mL',
            'ng/uL',
            'Molar',
        ));

        $statuses = implode(', ', array(
            'Available',
            'Depleted',
            'Destroyed',
            'Shipped',
            'Incoming',
        ));

        foreach ($prodRequestInputSamples as $prodRequestInputSample) {

            $current = 0;

            $serializedInputSample = json_decode($this->getSerializationHelper()->serialize($prodRequestInputSample, array('template')), true);

            $data = new Dot($serializedInputSample);

            foreach ($sampleTypeMapping as $label => $column) {

                $num = $currentSample + 1;
                $cell = $aRange[$current] . $num;

                $style = $objPHPExcel->getActiveSheet()->getStyle($cell);

                // if (in_array($label, $protectedLabels)) {

                //     $style->applyFromArray(array(
                //         'fill' => array(
                //             'type' => \PHPExcel_Style_Fill::FILL_SOLID,
                //             'color' => array('rgb' => 'fce7c2')
                //         )
                //     ));

                //     $style
                //         ->getProtection()
                //         ->setLocked(
                //             \PHPExcel_Style_Protection::PROTECTION_PROTECTED
                //         )
                //     ;

                // } else {

                //     $objPHPExcel->getActiveSheet()
                //     ->getStyle($cell)
                //     ->getProtection()
                //     ->setLocked(
                //         \PHPExcel_Style_Protection::PROTECTION_UNPROTECTED
                //     );

                // }

                if ($label == 'Storage Container') {

                    $objValidation = $objPHPExcel->getActiveSheet()->getCell($cell)->getDataValidation();
                    $objValidation->setType( \PHPExcel_Cell_DataValidation::TYPE_LIST );
                    $objValidation->setErrorStyle( \PHPExcel_Cell_DataValidation::STYLE_INFORMATION );
                    $objValidation->setAllowBlank(false);
                    $objValidation->setShowInputMessage(true);
                    $objValidation->setShowErrorMessage(true);
                    $objValidation->setShowDropDown(true);
                    $objValidation->setErrorTitle('Input error');
                    $objValidation->setError('Value is not in list.');
                    $objValidation->setPromptTitle('Pick from list');
                    $objValidation->setPrompt('Please pick a value from the drop-down list.');
                    $objValidation->setFormula1('"'.$storageContainerNames.'"');

                }

                if ($label == 'Concentration Units') {

                    $objValidation = $objPHPExcel->getActiveSheet()->getCell($cell)->getDataValidation();
                    $objValidation->setType( \PHPExcel_Cell_DataValidation::TYPE_LIST );
                    $objValidation->setErrorStyle( \PHPExcel_Cell_DataValidation::STYLE_INFORMATION );
                    $objValidation->setAllowBlank(false);
                    $objValidation->setShowInputMessage(true);
                    $objValidation->setShowErrorMessage(true);
                    $objValidation->setShowDropDown(true);
                    $objValidation->setErrorTitle('Input error');
                    $objValidation->setError('Value is not in list.');
                    $objValidation->setPromptTitle('Pick from list');
                    $objValidation->setPrompt('Please pick a value from the drop-down list.');
                    $objValidation->setFormula1('"' . $concentrationUnits . '"');

                }

                if ($label == 'Status') {
                    $objValidation = $objPHPExcel->getActiveSheet()->getCell($cell)->getDataValidation();
                    $objValidation->setType( \PHPExcel_Cell_DataValidation::TYPE_LIST );
                    $objValidation->setErrorStyle( \PHPExcel_Cell_DataValidation::STYLE_INFORMATION );
                    $objValidation->setAllowBlank(false);
                    $objValidation->setShowInputMessage(true);
                    $objValidation->setShowErrorMessage(true);
                    $objValidation->setShowDropDown(true);
                    $objValidation->setErrorTitle('Input error');
                    $objValidation->setError('Value is not in list.');
                    $objValidation->setPromptTitle('Pick from list');
                    $objValidation->setPrompt('Please pick a value from the drop-down list.');
                    $objValidation->setFormula1('"' . $statuses . '"');

                }

                if (array_key_exists('mtm', $column) && $column['mtm']) {
                    $itemIds = array();
                    foreach ($data->get($column['prop']) as $item) {
                        $itemIds[] = $item[$column['bindTo']];
                    }
                    if (count($itemIds)) {
                        $objPHPExcel->getActiveSheet()->getCell($cell)->setValue(implode(',', $itemIds));
                    }
                } else {
                    $objPHPExcel->getActiveSheet()->getCell($cell)->setValue($data->get($column['bindTo']));
                }

                $current++;
            }

            $currentSample++;

        }

        $objPHPExcel->getActiveSheet()->getProtection()->setSheet(true);
        $objPHPExcel->getActiveSheet()->getProtection()->setSort(true);
        $objPHPExcel->getActiveSheet()->getProtection()->setInsertRows(true);
        $objPHPExcel->getActiveSheet()->getProtection()->setFormatCells(true);

        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);

        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

        $response = new Response();
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment;filename=test.xlsx');

        ob_start();
        $objWriter->save('php://output');
        $content = ob_get_contents();
        ob_end_clean();
        $response->setContent($content);

        return $response;

    }

}

<?php

namespace Carbon\ApiBundle\Controller\Storage;

// VIOLATION -- Common really should not make assertions about the location of anything outside of common.
use AppBundle\Entity\Storage\Division;

use Carbon\ApiBundle\Serializer\Dot;
use Carbon\ApiBundle\Controller\CarbonApiController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\SerializationContext;

/*

    Outstanding issues:
        The division match route is filtering the number of elements down after is cutting it up into groups of 10.
            For further clarification: If the filter on the grid is set to return 10 results but 6 of the results which would appear in the normal filterfree result set then it will instead return 4 elements.


        CarbonGrid is resetting the orderby when you move to another page.

*/

// We should declare this as abstract and make it so that "divison" and "RESOURCE_ENTITY" are both only listed on the class that extends this one...
abstract class BaseDivisionController extends CarbonApiController
{
    /**
     * @var string The namespace of the resource entity
     */
    const RESOURCE_ENTITY = "AppBundle\Entity\Storage\Division"; // VIOLATION -- Common should not depend upon namespaces outside of common

    /**
     * @var string The form type for this resource
     */
    const FORM_TYPE = "division";

    // Rewritten by Taylor -- The depth limitations on the serialization of children was resulting in problems where the divisionsampeltype, divisonstoragecontainer...etc were not being serialized
    // In order to get around this problem while maintaining the performance characteristics of the original I had to sidestep the serialization helper so that I could use a custom serialization context ...
    // The use of the custom context to limit serialization made it so that I could raise the max depth on children to where it could serialize children -> divisionviewers -> user -> avatar attachment and other similar cases with access governors

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
        // return parent::handleGet();
        // $isDataTableRequest = $this->isDataTableRequest($request);

        // $entityRepository = $this->getEntityRepository();
        // $request = $this->getRequest();
        // $request = json_decode($request->getContent(), true);
        // $data = $this->container->get('doctrine.orm.default_entity_manager')->getRepository(static::RESOURCE_ENTITY)->find(365);

        // $isDataTableRequest = $this->isDataTableRequest($request);

        // $data = $this->getSerializationHelper()->serialize(
        //     $this->getGrid($isDataTableRequest)->getResult($this->getEntityRepository())
        // );

        // return $this->getJsonResponse($data);

        $request = $this->getRequest();
        $isDataTableRequest = $this->isDataTableRequest($request);

        $context = SerializationContext::create()->setGroups(array(
            'default',
            // 'tree',
            'samples',
            'parent',
            'children',
            'parent' => array(
                'default',
                'children',
                'parent',
                'samples',
                'tree',
                'viewers',
                'editors',
                'groupViewers',
                'groupEditors',
                'conatiners',
                'sampleTypes',
                'parent' => array(
                    'default',
                    'children',
                    'parent',
                    'samples',
                    'tree',
                    'viewers',
                    'editors',
                    'groupViewers',
                    'groupEditors',
                    'conatiners',
                    'sampleTypes',
                    'parent' => array(
                        'default',
                        // 'parent',
                        // 'parent' => array(
                        //     'parent',
                        //     'default',
                        //     'parent' => array(
                        //         'default',
                        //         'parent'
                        //     )
                        // )
                    )
                )
            ),
            'children' => array(
                'default',
                'viewers',
                'editors',
                'groupViewers',
                'groupEditors',
                'sampleTypes',
                'containers',
                'divisionGroupViewers' => array(
                    'default',
                    'group' => array(
                        'default'
                    )
                ),
                'divisionGroupEditors' => array(
                    'defualt',
                    'group' => array(
                        'default'
                    )
                ),
                'divisionViewers' => array(
                    'default',
                    'user' => array(
                        'default'
                    )
                ),
                'divisionEditors' => array(
                    'default',
                    'user' => array(
                        'default'
                    )
                ),
                'divisionStorageContainers' => array(
                    'default',
                    'storageContainer' => array(
                        'default'
                    )
                ),
                'divisionSampleTypes' => array(
                    'default',
                    'sampleType' => array(
                        'default'
                    )
                )
            )
        ));

        $context->enableMaxDepthChecks();

        $data = $this->getSerializationHelper()->serializeWithContext(
            $this->getGrid($isDataTableRequest)->getResult($this->getEntityRepository()), $context
        );

        // $tree = $this->getSerializationHelper()->serializeWithContext($data, $context, 'json');
        return $this->getJsonResponse($data);

        // $tree = $this->getSerializationHelper()->getSerializer()->serialize($data, 'json', $context);
        // $tree = json_decode($tree, true);
        // $tree = json_encode($tree);
        // return $this->getJsonResponse($tree);

        // return parent::handleGet();
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

    // Are we even hitting this route????
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

        // VIOLATION -- this should really not make assertions about the location of classes in common.
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


    // This function is having problems where it is returning fewer results than expected when there is a division
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
        // buildMatchQuery

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

    // This is the same function that creates input excels in production controller but with a single line changed to grab samples associated with a storage division instead of a request.
    /**
     * Handles the HTTP GET request for exporting the samples in a division to an excel sheet.
     *
     * @Route("/storage/division/{id}/excel-download", name="excel_download")
     * @Method("GET")
     *
     * @return Response
     */
    public function excelDownload($id){

        //$request = $this->getRequest();
        //$data = json_decode($request->getContent(), true);

        $fileName = 'Request ' . (string) $id . ' Input Samples Template.xlsx';

        $objPHPExcel = new \PHPExcel();

        $prodRequest = $this->getEntityManager()->getRepository('AppBundle\Entity\Storage\Division')->find($id); // VIOLATION
        $prodRequestInputSamples = $prodRequest->getSamples();

        //If there are no samples in the division just return an empty response. Will prevent this endpoint from being hit in that event
        if(count($prodRequestInputSamples)==0){
            $test = new Response();
            return $test;
        }

        $iterator = $prodRequestInputSamples->getIterator();

        $iterator->uasort(function($a,$b){
            return ((ord($a->getDivisionRow()) * 100 + $a->getDivisionColumn() ) < (ord($b->getDivisionRow()) * 100 + $b->getDivisionColumn())) ? -1 : 1;
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

        // VIOLATION -- Common should not make assertions about the locations of things outside of common
        $storageContainers = $this->getEntityManager()->getRepository('AppBundle\\Entity\\Storage\\StorageContainer')->findAll();

        $storageContainerNames = array();

        foreach ($storageContainers as $storageContainer) {
            $storageContainerNames[] = $storageContainer->getName();
        }

        // VIOLATION -- This makes assertions about the Sampletype implemenation -- allowed units should not be in common
        $storageContainerNames = implode(', ', $storageContainerNames);
        $concentrationUnits = implode(', ', array(
            'mg/mL',
            'ng/uL',
            'cells/ml',
            'cells/ul',
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

                    $objPHPExcel->getActiveSheet()
                    ->getStyle($cell)
                    ->getProtection()
                    ->setLocked(
                        \PHPExcel_Style_Protection::PROTECTION_UNPROTECTED
                    );

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

        // $objPHPExcel->getActiveSheet()->getProtection()->setSheet(true);
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

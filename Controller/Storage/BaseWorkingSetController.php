<?php

namespace Carbon\ApiBundle\Controller\Storage;

use AppBundle\Entity\Storage\Sample;

use Carbon\ApiBundle\Controller\CarbonApiController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Carbon\ApiBundle\Serializer\Dot;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class BaseWorkingSetController extends CarbonApiController
{
    /**
     * @var string The namespace of the resource entity
     */
    const RESOURCE_ENTITY = "AppBundle\Entity\Storage\WorkingSet";

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
            'roles' => array('ROLE_USER'),
        ),
        'DELETE' => array(
            'roles' => array('ROLE_USER'),
        )
    );

    protected $resourceLinkMap = array(
        'sample' => array(
            'returnedEntity' => 'Carbon\ApiBundle\Entity\User',
            'joinColumn' => 'workingSetId',
            'whereColumn' => 'sampleId',
        ),
        'user' => array(
            'returnedEntity' => 'AppBundle\Entity\Storage\Sample',
            'joinColumn' => 'sampleId',
            'whereColumn' => 'createdById',
        )
    );

    /**
     * @Route("/storage/working-set-sample/{type}/{id}", name="working_set_sample_get")
     * @Method("GET")
     *
     * @return Response
     */
    public function getAction($type, $id)
    {
        return parent::handleMTMGet($type, $id);
    }

    // Add a post option here


    // Add a delete option here


    // Also need to add a form type.


    // Generate a bulk update file

    /**
     * Handles the HTTP GET request for creating a bulk update excel sheet for your working set.
     *
     * @Route("/storage/working-set-bulk/excelDownload", name="working_set_excel_download")
     * @Method("GET")
     *
     * @return Response
     */
    public function workingSetExcelDownload(){

        //$request = $this->getRequest();
        //$data = json_decode($request->getContent(), true);

        $fileName = 'Working set updates template.xlsx';

        $objPHPExcel = new \PHPExcel();

        $workingSetEntries = $this->getEntityManager()->getRepository('AppBundle\Entity\Storage\WorkingSet')->findBy(array('user' => $this->getUser()));
        $samples = array();
        foreach($workingSetEntries as $workingSetEntry){
            $samples[] = $workingSetEntry->getSample();
        }
        $prodRequestInputSamples = $samples;

        //If there are no samples in the division just return an empty response. Will prevent this endpoint from being hit in that event
        if(count($prodRequestInputSamples)==0){
            $test = new Response();
            return $test;
        }

        // $iterator = $prodRequestInputSamples->getIterator();

        // $iterator->uasort(function($a,$b){
        //     return ((ord($a->getDivisionRow()) * 100 + $a->getDivisionColumn() ) < (ord($b->getDivisionRow()) * 100 + $b->getDivisionColumn())) ? -1 : 1;
        // });
        // $prodRequestInputSamples = new ArrayCollection(iterator_to_array($iterator));

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



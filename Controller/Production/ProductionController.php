<?php

namespace Carbon\ApiBundle\Controller\Production;

use Carbon\ApiBundle\Controller\CarbonApiController;
use Carbon\ApiBundle\Entity\Production\BaseRequest;
use Carbon\ApiBundle\Serializer\Dot;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProductionController extends CarbonApiController
{
    /**
     * @Route("/production/download-input-template", name="production_input_template_download")
     * @Method("POST")
     *
     * @return Response
     */
    public function downloadInputTemplateAction()
    {
        $request = $this->getRequest();
        $data = json_decode($request->getContent(), true);
        $inputTemplateType = $data['inputTemplateType'];

        if ($inputTemplateType === 'CSV') {
            return $this->getCSVInputTemplateResponse();
        }

        if ($inputTemplateType === 'EXCEL') {
            return $this->getInputExcelTemplateResponse();
        }

        if ($inputTemplateType === 'GRIDFORM') {
            return $this->getInputGridformTemplateResponse();
        }

        return $this->handleError();

   }

   /**
     * @Route("/production/download-output-template", name="production_output_template_download")
     * @Method("POST")
     *
     * @return Response
     */
    public function downloadOutputTemplateAction()
    {
        $request = $this->getRequest();
        $data = json_decode($request->getContent(), true);
        $outputTemplateType = $data['outputTemplateType'];

        if ($outputTemplateType === 'CSV') {
            return $this->getCSVOutputTemplateResponse();
        }

        if ($outputTemplateType === 'EXCEL') {
            return $this->getOutputExcelTemplateResponse();
        }

        if ($outputTemplateType === 'GRIDFORM') {
            return $this->getOutputGridformTemplateResponse();
        }

        return $this->handleError();

    }


    protected function handleError()
    {

        return $this->getJsonResponse($this->getSerializationHelper()->serialize(array('violations' => array(array("Your request did not contain a template type")))), 400);

    }

    // grid form should be altered to return the catalog instead of just the catalog name
   // Testing portion
   private function getOutputGridformTemplateResponse()
    {
        $em = $this->getEntityManager();
        $request = $this->getRequest();
        $data = json_decode($request->getContent(), true);
        $totalOutputSamples = $data['totalOutputSamples'];
        $outputSampleDefaults = $data['outputSampleDefaults'];

        $entDetRepo = $em->getRepository('Carbon\ApiBundle\Entity\EntityDetail');

        $gridFormResponse = array();

        if ($outputSampleDefaults == null) {
            $outputSampleDefaults = [];
        }

        // If only one default is provided then take the count and clone it $totalOutputSamples times
        if (!$this->isMultiDimArray($outputSampleDefaults)) {
            $temp = array();
            for ($i =0; $i < $totalOutputSamples; $i++) {
                $temp[] = $outputSampleDefaults;
            }
            $outputSampleDefaults = $temp;
        }

        if (array_key_exists('outputSampleType', $data)) {
            $outputSampleTypeId = $data['outputSampleType']['id'];
        } else {
            $outputSampleTypeId = 1;
        }

        $importer = $this->container->get('sample.importer'); // This is going to change to grab a genetic importer at some point

        // Build the columns header list
        $headers = $importer->getGridFormColumnHeaders();
        $gridFormResponse['headers'] = $headers;

        // Build this list of initial values and send them back
        $gridFormResponse['content'] = array();

        // Check if it is for a relation -- if it is then we need to return an object instead of a string
        // If defaults are provided
        foreach ($outputSampleDefaults as $osd) {

            // $gridFormResponse[] = $osd;
            // Loop over the resultset

            foreach ($osd as $key => $value){

                $meta = $headers[$key];

                if ($meta['type'] == 'relation') { // if array key exists would add robustness

                    $entDet = $entDetRepo->find($meta['entityDetailId']); // if array key exists would add robustness

                    // If the object that is passed is a string instead of a list of properties then we need to look it up
                    if (!is_array($value)) {

                        // echo $value;
                        $specificRepo = $em->getRepository($entDet->getObjectClassName());
                        $found =  $specificRepo->findBy(array($meta['searchProp'] => $value));
                        // $target and $donor are currently passed as ids instead of as names...

                        if (!$found){

                            $classname = $entDet->getObjectClassName();
                            $found = new $classname();
                            $found->setName($value);

                        }

                        $osd[$key] = $found;

                    }

                }

            }

            $gridFormResponse['content'][] = $osd;

        }

        $serialized = $this->getSerializationHelper()->serialize($gridFormResponse);
        $response = $this->getJsonResponse($serialized);

        return $response;

    }

   private function getInputGridformTemplateResponse()
    {
        $em = $this->getEntityManager();
        $request = $this->getRequest();
        $data = json_decode($request->getContent(), true);

        $prodRequest = $this->getEntityManager()->getRepository($data['entity'])->find($data['id']);
        $prodRequestInputSamples = $prodRequest->getInputSamples();
        $importer = $this->container->get('sample.importer');

        // fetch the things

        $gridFormResponse = array();

        $gridFormResponse['headers'] = $importer->getGridFormColumnHeaders();

        $gridFormResponse['content'] = $prodRequestInputSamples;

        $serialized = $this->getSerializationHelper()->serialize($gridFormResponse);
        $response = $this->getJsonResponse($serialized);

        return $response;

    }

   // End of testing portion

    /**
     * @Route("/production/complete", name="production_complete")
     * @Method("POST")
     *
     * @return Response
     */
    public function completeAction()
    {
        $request = $this->getRequest();
        $data = json_decode($request->getContent(), true);
        $requestObjectFormData = $data['requestObject'];
        $requestFormType = $data['requestFormType'];

        $prodRequest = $this->getEntityManager()->getRepository($data['entity'])->find($data['id']);
        $em = $this->getEntityManager();

        if ($data['resultSampleIds']) {

            $outputSampleIds = $data['resultSampleIds'];

            $samples = $em->getRepository('AppBundle\Entity\Storage\Sample')->findBy(array('id' => $outputSampleIds));

            $prodRequestMeta = $em->getMetadataFactory()->getMetadataFor(get_class($prodRequest));
            $prodRequestMapping = $prodRequestMeta->getAssociationMapping('outputSamples');
            $targetEntity = $prodRequestMapping['targetEntity'];

            $requestOutputSamples = array();
            foreach ($samples as $sample) {
                $requestOutputSample = new $targetEntity();
                $requestOutputSample->setRequest($prodRequest);
                $requestOutputSample->setSample($sample);
                $em->persist($requestOutputSample);
                $requestOutputSamples[] = $requestOutputSample;
            }

            $prodRequest->setOutputSamples($requestOutputSamples);

        }

        if ($data['depletedAllInputSamples'] == true) {
            $prodRequest = $em->getRepository($data['entity'])->find($data['id']);
            $inputSamples = $prodRequest->getInputSamples();
            foreach ($inputSamples as $inputSample) {
                $inputSample->getSample()->setStatus('Depleted');
            }
        }

        $em->flush();

        $form = $this->createForm($requestFormType, $prodRequest);
        $form->submit($requestObjectFormData, true);

        $em->flush();

        return $this->getJsonResponse(json_encode(array('success' => 'success')));
    }

    public function getCSVInputTemplateResponse()
    {
        $request = $this->getRequest();
        $data = json_decode($request->getContent(), true);

        $prodRequest = $this->getEntityManager()->getRepository($data['entity'])->find($data['id']);
        $prodRequestInputSamples = $prodRequest->getInputSamples();
        $prodRequestInputSample = $prodRequestInputSamples[0]->getSample();

        $importer = $this->container->get('sample.importer');

        $fileName = 'Request ' . $data['id'] . ' Input Samples Template.csv';

        $content = $importer->getTemplateContent($prodRequestInputSample->getSampleType());
        $content = 'Id,' . $content;

        $sampleTypeMapping = $importer->getMapping($prodRequestInputSample->getSampleType());

        $sampleTypeMapping = array_merge(array(
            'Id' => array(
                'prop' => 'id',
                'bindTo' => 'id',
                'errorProp' => array('id'),
            )
        ), $sampleTypeMapping);

        foreach ($prodRequestInputSamples as $prodRequestInputSample) {

            $serializedInputSample = json_decode($this->getSerializationHelper()->serialize($prodRequestInputSample->getSample(), array('template')), true);

            $data = new Dot($serializedInputSample);

            $content .= "\n";

            foreach ($sampleTypeMapping as $label => $column) {

                $content .= $data->get($column['bindTo']) . ',';

            }

        }

        $response = new Response();
        $response->headers->set('Content-Type', 'application/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="'. $fileName .'";');

        $response->setContent($content);

        return $response;
    }

    private function getInputExcelTemplateResponse()
    {
        $request = $this->getRequest();
        $data = json_decode($request->getContent(), true);

        $fileName = 'Request ' . $data['id'] . ' Input Samples Template.xls';

        $objPHPExcel = new \PHPExcel();

        $prodRequest = $this->getEntityManager()->getRepository($data['entity'])->find($data['id']);
        $prodRequestInputSamples = $prodRequest->getInputSamples();
        $prodRequestInputSample = $prodRequestInputSamples[0]->getSample();
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
            'cells/mL',
            'cells/uL'
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

            $serializedInputSample = json_decode($this->getSerializationHelper()->serialize($prodRequestInputSample->getSample(), array('template')), true);

            $data = new Dot($serializedInputSample);

            foreach ($sampleTypeMapping as $label => $column) {

                $num = $currentSample + 1;
                $cell = $aRange[$current] . $num;

                $style = $objPHPExcel->getActiveSheet()->getStyle($cell);

                if (in_array($label, $protectedLabels)) {

                    $style->applyFromArray(array(
                        'fill' => array(
                            'type' => \PHPExcel_Style_Fill::FILL_SOLID,
                            'color' => array('rgb' => 'fce7c2')
                        )
                    ));

                    $style
                        ->getProtection()
                        ->setLocked(
                            \PHPExcel_Style_Protection::PROTECTION_PROTECTED
                        )
                    ;

                } else {

                    $objPHPExcel->getActiveSheet()
                    ->getStyle($cell)
                    ->getProtection()
                    ->setLocked(
                        \PHPExcel_Style_Protection::PROTECTION_UNPROTECTED
                    );

                }

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
        $response->headers->set('Content-Type', 'application/vnd.ms-excel');
        $response->headers->set('Content-Disposition', 'attachment;filename=test.xlsx');

        ob_start();
        $objWriter->save('php://output');
        $content = ob_get_contents();
        ob_end_clean();
        $response->setContent($content);

        return $response;
    }

    private function getCSVOutputTemplateResponse()
    {
        $request = $this->getRequest();
        $data = json_decode($request->getContent(), true);

        $outputSampleDefaults = $data['outputSampleDefaults'];
        $prodRequest = $this->getEntityManager()->getRepository($data['entity'])->find($data['id']);

        $importer = $this->container->get('sample.importer');
        $sampleType = $this->getEntityManager()->getRepository('AppBundle\\Entity\\Storage\\SampleType')->findOneByName($outputSampleDefaults['sampleType']);
        $sampleTypeMapping = $importer->getMapping($sampleType);

        $fileName = 'Request ' . $data['id'] . ' Output Samples Template.csv';

        $content = $importer->getTemplateContent($sampleType);

        $count = 0;


        while ($count < $data['totalOutputSamples']) {

            $content .= "\n";

            foreach ($sampleTypeMapping as $label => $column) {

                if (array_key_exists($column['prop'], $outputSampleDefaults)) {
                    $content .= $outputSampleDefaults[$column['prop']] . ',';
                } else {
                    $content .= ',';
                }
            }

            $count++;

        }

        $response = new Response();
        $response->headers->set('Content-Type', 'application/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="'. $fileName .'";');

        $response->setContent($content);

        return $response;
    }


    //check to see if something is a multidimensional array... The objects that we were getting from the front end were unfortunately passing the is_arry check regardless of whether they were objects or arrays of objects...
    protected function isMultiDimArray($arr)
    {
        foreach ($arr as $a)
        {
            if (is_array($a)){
                return true;
            }
        }
        return false;
    }

    private function getOutputExcelTemplateResponse()
    {
        $request = $this->getRequest();
        $data = json_decode($request->getContent(), true);
        $totalOutputSamples = $data['totalOutputSamples'];
        $outputSampleDefaults = $data['outputSampleDefaults'];

        if ($outputSampleDefaults == null ) {
            $outputSampleDefaults = [];
        }

        if (!$this->isMultiDimArray($outputSampleDefaults)) {
            $temp = array();
            for ($i =0; $i < $totalOutputSamples; $i++) {
                $temp[] = $outputSampleDefaults;
            }
            $outputSampleDefaults = $temp;
        }

        if (array_key_exists('id', $data)) {
            $fileName = 'Request ' . $data['id'] . ' Output Samples Template.xls';
        } else {
            $fileName = 'Sample Import Template.xls';
        }

        $objPHPExcel = new \PHPExcel();

        if (array_key_exists('outputSampleType', $data)) {
            $outputSampleTypeId = $data['outputSampleType']['id'];
        } else {
            $outputSampleTypeId = 1;
        }

        $sampleType = $this->getEntityManager()->getRepository('AppBundle\\Entity\\Storage\\SampleType')->find($outputSampleTypeId);

        $importer = $this->container->get('sample.importer');
        $sampleTypeMapping = $importer->getMapping($sampleType); //This does not need an argument... ? ? ?

        $currentSample = 0;

        $aRange = range('A', 'Z');
        $current = 0;

        foreach ($sampleTypeMapping as $label => &$column) {
            $objPHPExcel->getActiveSheet()->getColumnDimension($aRange[$current])->setWidth(15);
            $cell = $objPHPExcel->getActiveSheet()->getCell($aRange[$current] . '1');
            $cell->setValue($label);
            $style = $objPHPExcel->getActiveSheet()->getStyle($aRange[$current] . '1');
            $style->getFont()->setBold(true);

            $current++;
        }
        unset($column); //Dear god why?

        $currentSample = 1;

        $protectedLabels = array();
        // $protectedLabels = array(
        //     'Id',
        //     'Sample Type',
        //     'Catalog',
        //     'Lot',
        //     'Division',
        //     'Division Row',
        //     'Division Column',
        // );

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
            'cells/mL',
            'cells/uL',
        ));

        $volumeUnits = implode(', ', array(
            'mL',
            'uL'
        ));

        $statuses = implode(', ', array(
            'Available',
            'Depleted',
            'Destroyed',
            'Shipped',
            'Incoming',
        ));

        $currentOutputSampleIndex = 0;
        while ($currentOutputSampleIndex < $totalOutputSamples) {

            $current = 0;
            foreach ($sampleTypeMapping as $label => $column) {

                $num = $currentSample + 1;
                $cell = $aRange[$current] . $num;

                $style = $objPHPExcel->getActiveSheet()->getStyle($cell);

                if (in_array($label, $protectedLabels)) {

                    $style->applyFromArray(array(
                        'fill' => array(
                            'type' => \PHPExcel_Style_Fill::FILL_SOLID,
                            'color' => array('rgb' => 'fce7c2')
                        )
                    ));

                    $style
                        ->getProtection()
                        ->setLocked(
                            \PHPExcel_Style_Protection::PROTECTION_PROTECTED
                        )
                    ;

                } else {

                    $objPHPExcel->getActiveSheet()
                    ->getStyle($cell)
                    ->getProtection()
                    ->setLocked(
                        \PHPExcel_Style_Protection::PROTECTION_UNPROTECTED
                    );

                }

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

                if ($label == 'Volume Units') {

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
                    $objValidation->setFormula1('"' . $volumeUnits . '"');

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
                if (array_key_exists($column['prop'], $outputSampleDefaults[$currentOutputSampleIndex])) {
                    if (is_array($outputSampleDefaults[$currentOutputSampleIndex][$column['prop']])) {
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
                        $objValidation->setFormula1('"' . implode(', ', $outputSampleDefaults[$currentOutputSampleIndex][$column['prop']]) . '"');
                        $objPHPExcel->getActiveSheet()->getCell($cell)->setValue($outputSampleDefaults[$currentOutputSampleIndex][$column['prop']][0]);
                    } else {

                        $objPHPExcel->getActiveSheet()->getCell($cell)->setValue($outputSampleDefaults[$currentOutputSampleIndex][$column['prop']]);

                    }

                }

                $current++;
            }

            $currentSample++;
            $currentOutputSampleIndex++;

        }
        if (array_key_exists('id', $data)) {
            $objPHPExcel->getActiveSheet()->getProtection()->setSheet(true);
            $objPHPExcel->getActiveSheet()->getProtection()->setSort(true);
            $objPHPExcel->getActiveSheet()->getProtection()->setInsertRows(true);
            $objPHPExcel->getActiveSheet()->getProtection()->setFormatCells(true);
        }

        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);

        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

        $response = new Response();
        $response->headers->set('Content-Type', 'application/vnd.ms-excel');
        $response->headers->set('Content-Disposition', 'attachment;filename=test.xlsx');

        ob_start();
        $objWriter->save('php://output');
        $content = ob_get_contents();
        ob_end_clean();
        $response->setContent($content);

        return $response;
    }

}

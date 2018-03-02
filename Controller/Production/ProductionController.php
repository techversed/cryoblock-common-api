<?php

namespace Carbon\ApiBundle\Controller\Production;

use Carbon\ApiBundle\Controller\CarbonApiController;
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

        $fileName = 'Request ' . $data['id'] . ' Input Samples Template.xls';

        $objPHPExcel = new \PHPExcel();


        // $configs = "DUS800, DUG900+3xRRUS, DUW2100, 2xMU, SIU, DUS800+3xRRUS, DUG900+3xRRUS, DUW2100";


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
        ));

        $statuses = implode(', ', array(
            'Available',
            'Depleted',
            'Destroyed',
            'Shipped'
        ));


        foreach ($prodRequestInputSamples as $prodRequestInputSample) {

            $current = 0;

            $serializedInputSample = json_decode($this->getSerializationHelper()->serialize($prodRequestInputSample->getSample()), true);

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

                $objPHPExcel->getActiveSheet()->getCell($cell)->setValue($data->get($column['bindTo']));
                $current++;
            }

            $currentSample++;

        }



        // $objPHPExcel->getActiveSheet()
        //     ->getStyle('A1:A20')
        //     ->getProtection()
        //     ->setLocked(
        //         \PHPExcel_Style_Protection::PROTECTION_PROTECTED
        // );

        // $objPHPExcel->getActiveSheet()
        //     ->getStyle('B1:B20')
        //     ->getProtection()
        //     ->setLocked(
        //         \PHPExcel_Style_Protection::PROTECTION_UNPROTECTED
        // );
        $objPHPExcel->getActiveSheet()->getProtection()->setSheet(true);
        $objPHPExcel->getActiveSheet()->getProtection()->setSort(true);
        $objPHPExcel->getActiveSheet()->getProtection()->setInsertRows(true);
        $objPHPExcel->getActiveSheet()->getProtection()->setFormatCells(true);

        // $objValidation = $objPHPExcel->getActiveSheet()->getCell('B5')->getDataValidation();
        // $objValidation->setType( \PHPExcel_Cell_DataValidation::TYPE_LIST );
        // $objValidation->setErrorStyle( \PHPExcel_Cell_DataValidation::STYLE_INFORMATION );
        // $objValidation->setAllowBlank(false);
        // $objValidation->setShowInputMessage(true);
        // $objValidation->setShowErrorMessage(true);
        // $objValidation->setShowDropDown(true);
        // $objValidation->setErrorTitle('Input error');
        // $objValidation->setError('Value is not in list.');
        // $objValidation->setPromptTitle('Pick from list');
        // $objValidation->setPrompt('Please pick a value from the drop-down list.');
        // $objValidation->setFormula1('"'.$configs.'"');

        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);


        // Save Excel 95 file
        // echo date('H:i:s') , " Write to Excel5 format" , EOL;
        // $callStartTime = microtime(true);
        // header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        // header('Content-Disposition: attachment;filename="myfile.xlsx"');
        // header('Cache-Control: max-age=0');

        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

        $response = new Response();
        $response->headers->set('Content-Type', 'application/vnd.ms-excel');
        $response->headers->set('Content-Disposition', 'attachment;filename=test.xlsx');

        ob_start();
        $objWriter->save('php://output');
        $content = ob_get_contents();
        ob_end_clean();
        $response->setContent($content);

        // $response->headers->set('Pragma', 'public');
        // $response->headers->set('Cache-Control', 'maxage=1');

        // $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        // $objWriter->save('/tmp/populate.xls');

        // $readerObject = \PHPExcel_IOFactory::createReader('Excel5');
        // $phpExcelObject = $readerObject->load('/tmp/populate.xls');
        // $writer = $this->get('phpexcel')->createWriter($phpExcelObject, 'Excel5');
        // $response = $this->get('phpexcel')->createStreamedResponse($writer);
        // $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        // $response->headers->set('Content-Disposition', 'attachment;filename=test.xls');
        // $response->headers->set('Pragma', 'public');
        // $response->headers->set('Cache-Control', 'maxage=1');

        return $response;
        // $response = new Response(ob_get_clean());
        // $response->headers->set('Content-Type', 'application/vnd.ms-excel');
        // $dispositionHeader = $response->headers->makeDisposition(
        //     ResponseHeaderBag::DISPOSITION_ATTACHMENT,
        //     'PhpExcelFileSample.xlsx'
        // );
        // $response->headers->set('Content-Disposition', $dispositionHeader);
        // header('Content-type: application/vnd.ms-excel');

        // It will be called file.xls
        // header('Content-Disposition: attachment; filename="file.xls"');

        // // Write file to the browser
        // ob_start();
        // $objWriter->save('php://output');
        // $content = ob_get_contents();
        // ob_end_clean();

        // $objWriter->save('/tmp/populate.xls');
        // $objWriter->save('/tmp/populate.xls');
        // $objWriter->save(/tmp);

        // $content = file_get_contents('/tmp/populate.xls');

        // $response->setContent($content);


        // unlink('/tmp/populate.xls');
        // return $response;
        die;







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

            $serializedInputSample = json_decode($this->getSerializationHelper()->serialize($prodRequestInputSample->getSample()), true);

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

        $prodRequest = $this->getEntityManager()->getRepository($data['entity'])->find($data['id']);
        $inputSamples = $prodRequest->getInputSamples();
        $inputSample = $inputSamples[0]->getSample();

        $importer = $this->container->get('sample.importer');

        $fileName = 'Request ' . $data['id'] . ' Template.csv';

        $content = $importer->getTemplateContent($inputSample->getSampleType());

        $count = 0;

        $sampleTypeMapping = $importer->getMapping($inputSample->getSampleType());

        $serializedInputSample = json_decode($this->getSerializationHelper()->serialize($inputSample), true);

        $data = new Dot($serializedInputSample);
        $nullColumns = array('division', 'divisionRow', 'divisionColumn', 'storageContainer');

        while ($count < $data['totalOutputSamples']) {

            $content .= "\n";

            foreach ($sampleTypeMapping as $label => $column) {

                if (in_array($column['prop'], $nullColumns)) {
                    $content .= ',';
                } else {
                    $content .= $data->get($column['bindTo']) . ',';
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

}

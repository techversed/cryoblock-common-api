<?php

namespace Carbon\ApiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\HttpFoundation\Response;
use Carbon\ApiBundle\Controller\CarbonApiController;

/*

    The standard means of storage is just going to a an array of objects -- can choose to persist them or not -- it really makes no difference

*/

class GridFormController extends CarbonApiController
{

    /**
     * @Route("/storage/sample-import/save", name="sample_import_save")
     * @Method("POST")
     *
     * @return Response
     */
    public function saveAction()
    {
        $request = $this->getRequest();
        $data = json_decode($request->getContent(), true);
        $catalogData = $data['catalogData'];

        $em = $this->getEntityManager();

        $samples = $data['entities'];

        $sampleIds = array();
        $createdSamples = array();

        // Check if new catalogs need to be created
            // If new catalogs are created edit the sample entries to contain the new names

        foreach ($samples as $sample) {

            if (array_key_exists('id', $sample)) {
                $entity = $em->getRepository('AppBundle:Storage\Sample')->find($sample['id']);
                $newlyCreated = false;
            } else {
                $entity = new Sample();
                $newlyCreated = true;
            }

            $form = $this->createForm('sample', $entity);
            $form->submit($sample);

            if (!$form->isValid()) {

                return $this->getFormErrorResponse($form);

            }

            if ($newlyCreated == true){
                $em->persist($entity);
            }

            if (!array_key_exists('id', $sample)) {
                $sampleIds[] = $entity->getId();
                $createdSamples[] = $entity;
            }

        }

        //THIS SUCKS
        /*

            Need to write a generic way of handling this

        */

        // if ($catalogData && !$catalogData['hasExistingCatalog'] && $catalogData['totalInputCatalogs'] > 1) {

        //     $catalog = $em->getRepository('AppBundle:Storage\Catalog')->findOneByName($catalogData['catalogName']);

        //     if (!$catalog) {
        //         throw new EntityNotFoundException(sprintf('Catalog not found with name %s', $catalogData['catalogName']));
        //     }

        //     foreach ($catalogData['catalogIds'] as $childCatalogId) {

        //         $childCatalog = $em->getRepository('AppBundle:Storage\Catalog')->find($childCatalogId);

        //         if (!$childCatalog) {
        //             throw new EntityNotFoundException(sprintf('Child catalog not found with id %s', $childCatalogId));
        //         }

        //         $parentCatalog = new ParentCatalog();
        //         $parentCatalog->setParentCatalog($catalog);
        //         $parentCatalog->setChildCatalog($childCatalog);
        //         $em->persist($parentCatalog);

        //     }

        // }

        $em->flush();

        $responseData = $this->getSerializationHelper()->serialize(array(
            'sampleIds' => $sampleIds,
            'samples' => $createdSamples,
        ));

        return $this->getJsonResponse($responseData);
    }


    /**
     * @Route("/grid-form/download/{entDetId}", name="grid_form_download")
     * @Method("POST")
     *
     * @return Response
     */
    public function downloadTemplateAction($entDetId)
    {

        $request = $this->getRequest();
        $data = json_decode($request->getContent(), true);
        $outputTemplateType = $data['outputTemplateType'];
        $entities = $data['entities'];

        $em = $this->getEntityManager();
        $entDet = $em->getRepository('Carbon\ApiBundle\Entity\EntityDetail')->find($entDetId);

        $importerClass = $entDet->getImporterClass();
        $importer = $this->container->get($importerClass);

        $gridContents = array();

        return $this->getOutputExcelTemplateResponse($gridContents, $importer);

        // $response = new Response();
        // return $response;
    }

    /**
     * @Route("/grid-form/validate/{entDetId}", name="grid_form_validate")
     * @Method("POST")
     *
     * @return Response
     */
    public function validateTemplate($entDetId)
    {

    }


    private function getOutputExcelTemplateResponse($gridContents, $importer)
    {

        $path = '\\' +  'AppBundle\Entity\Storage\Sample';

        $test = new $path;
        $test->setId(1);
        echo $test->getId();

        // $test = new \AppBundle\Entity\Storage\Sample;

        // getSampleGridFormColumnMap()

        $data = array('id'=>1);

        // End Test

        $totalOutputSamples = count($gridContents);
        $isUpdate = array_key_exists('id', $data);

        $filename = $isUpdate ? 'Request ' . $data['id'] . ' Output Samples Template.xls' : $fileName = 'Sample Import Template.xls';

        $objPHPExcel = new \PHPExcel();

        // $sampleType = $this->getEntityManager()->getRepository('AppBundle\\Entity\\Storage\\SampleType')->find($outputSampleTypeId);
        // $importer = $this->container->get('sample.importer');
        // $sampleTypeMapping = $importer->getMapping(); //This does not need an argument... ? ? ?

        $mapping = $importer->getEntityGridFormColumnMap();

        $currentSample = 0;

        $aRange = range('A', 'Z');
        $current = 0;

        // foreach ($sampleTypeMapping as $label => $column) {
        foreach ($mapping as $label => $column) {
            $objPHPExcel->getActiveSheet()->getColumnDimension($aRange[$current])->setWidth(15);
            $cell = $objPHPExcel->getActiveSheet()->getCell($aRange[$current] . '1');
            // $cell->setValue($label);
            $cell->setValue($column['name']);
            $style = $objPHPExcel->getActiveSheet()->getStyle($aRange[$current] . '1');
            $style->getFont()->setBold(true);

            $current++;
        }

        // unset($column); //Dear god why?

        $currentSample = 1;

        $protectedLabels = $isUpdate ? $importer->getUpdateProtectedLabels() : array();

        $currentOutputSampleIndex = 0;

        while ($currentOutputSampleIndex < $totalOutputSamples) {

            $current = 0;
            //instead of using sampleTypeMapping lets use somethign else
            // foreach ($sampleTypeMapping as $label => $column) {
            foreach ($mapping as $label => $column) {

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

                if($column['type'] == 'dropdown') {

                    $objValidation = $objPHPExcel->getActiveSheet()->getCell($cell)->getDataValidation();
                    $objValidation->setType( \PHPExcel_Cell_DataValidation::TYPE_LIST );
                    $objValidation->setErrorStyle( \PHPExcel_Cell_DataValidation::STYLE_INFORMATION );
                    $objValidation->setFormula1('"'.$storageContainerNames.'"');

                    if(isset($column['errorTitle'])) {


                        $objValidation->setErrorTitle('Input error');
                        $objValidation->setShowErrorMessage(true);
                    }
                    if(isset($column['error'])) {

                        $objValidation->setShowErrorMessage(true);
                        $objValidation->setError('Value is not in list.');

                    }
                    if(isset($column['promptitle'])) {

                        $objValidation->setShowDropDown(true);
                        $objValidation->setShowInputMessage(true);
                        $objValidation->setPromptTitle('Pick from list');

                    }
                    if(isset($column['prompt'])) {

                        $objValidation->setShowInputMessage(true);
                        $objValidation->setShowDropDown(true);
                        $objValidation->setPrompt('Please pick a value from the drop-down list.');

                    }
                    if(isset($column['allowBlank'])) {
                        $objValidation->setAllowBlank(false);
                    }

                    if(isset($column['acceptedValues'])) {
                        $objValidation->setFormula1('"' . $column['acceptedValues']. '"');
                    }
                }



// This section is all going to be replaced with something that is more generic -- handle it once with all of the tags


                // This section should be replaced.

                // This is going to have to be modified to work with what we are using

                // if (array_key_exists($column['prop'], $outputSampleDefaults[$currentOutputSampleIndex])) {
                //     if (is_array($outputSampleDefaults[$currentOutputSampleIndex][$column['prop']])) {
                //         $objValidation = $objPHPExcel->getActiveSheet()->getCell($cell)->getDataValidation();
                //         $objValidation->setType( \PHPExcel_Cell_DataValidation::TYPE_LIST );
                //         $objValidation->setErrorStyle( \PHPExcel_Cell_DataValidation::STYLE_INFORMATION );
                //         $objValidation->setAllowBlank(false);
                //         $objValidation->setShowInputMessage(true);
                //         $objValidation->setShowErrorMessage(true);
                //         $objValidation->setShowDropDown(true);
                //         $objValidation->setErrorTitle('Input error');
                //         $objValidation->setError('Value is not in list.');
                //         $objValidation->setPromptTitle('Pick from list');
                //         $objValidation->setPrompt('Please pick a value from the drop-down list.');
                //         $objValidation->setFormula1('"' . implode(', ', $outputSampleDefaults[$currentOutputSampleIndex][$column['prop']]) . '"');
                //         $objPHPExcel->getActiveSheet()->getCell($cell)->setValue($outputSampleDefaults[$currentOutputSampleIndex][$column['prop']][0]);
                //     } else {

                //         $objPHPExcel->getActiveSheet()->getCell($cell)->setValue($outputSampleDefaults[$currentOutputSampleIndex][$column['prop']]);

                //     }

                // }


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


    protected function readExcel($requestContent, $importer)
    {

    }

    protected function returnExcel()
    {

    }

    protected function readCsv($requestContent, $importer)
    {

    }

    protected function returnCSV()
    {

    }

    protected function readGridForm($requestContent, $importer)
    {

    }

    protected function returnGridForm()
    {

    }

    protected function validateGridForm($grodform)
    {

    }

    protected function commitChanges($gridform)
    {

    }

}

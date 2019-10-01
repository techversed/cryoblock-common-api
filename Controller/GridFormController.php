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

/*

    Gonna redo this from scratch

*/

/*
    We would like for grid forms to be powerful enough to create and update objects in the same action -- as a result we are going to need to make it so that

    At the current point in time we are not making it so that anything can be sent to these routes without a user being logged in first -- we have built code to make it so that users that are not logged in yet are assumed to be the utilities-services user.

    This class is going to replace the sample import controller and portion of the production controller.

    This is going to have a validate action which is going to run the data that was provided through the validators in the importer class

    This is going to have a complete class which commits the requested action to the database.

*/

/*
NOTES:


LIST OF OTHER CHANGES THAT ARE GOING TO BE NEEDED IN ORDER FOR THIS TO ALL WORK OUT.
    Importers may need additioanl properties
    Everything that you would like to update with this is going to need to have its own importer created
    Entity detail needs to have the name of the form that needs to be grabbed.
    Entity detail is going to need to have the importer stored for each type of element that is allowed to be imported with this mechanism
    May need to have multiple different functions for bulk import -- not sure at this time if it makes more sense for us to have separate importers for bulk and linker updates or if it makes sense to add new functions to the importer depending upon which action you are attempting to make
    Entity detail should be expanded to have update, create, delete permissions roles on it -- since we are going to try to make something generic that is going to work with all type of entities it is going to be absolutely essential for us to have a mechanism to set this for individual entity types instead of setting a standard in the controller.


*/

//JUNK notes area

/*

        // Need to quickly validate that it would be possible to do bulk updates of non-nested data using this method...
            // Lets start out by assuming that we are going to need to use this in order to perform bulk updates on samples... what needs to be added?
            // We would also need to add a property at the top level asking if it is a mtm update or if it is a bulk update of a single type of entity.


        // possible values for the 'updateType' key.
            // mtmParent -- used when the user is trying to adjust the properties on a parent and would like to add metadata to a linker table entry (something that would not be possible using the regular formtype setup
            // bulkEntity -- If there is no parent object and you would like to use the gridform essentially as a regular bulk (like the excel upload) then this can be specified.

        // This complete action is going to function in basically the same way that production controller operates...


        // foreach($entities as $entity)
        // {

        //     // Here are all of the things

        // }

        // $requestObjectFormData = $data['requestObject'];
        // $requestFormType = $data['requestFormType'];
        // $prodRequest = $em->getRepository($data['entity'])->find($data['id']);

        // This is not going to work yet

*/

class GridFormController extends CarbonApiController
{
    // public function __construct(

// New Version of this -- this is really just going to be a repackaged sample import controller.


// This is going to return the entities in the form which is requested
// Finish this up later

    // public function downloadOutputTemplateAction()
    // {
    //     $request = $this->getRequest();
    //     $data = json_decode($request->getContent(), true);
    //     $outputTemplateType = $data['outputTemplateType'];

    //     if ($outputTemplateType === 'CSV') {
    //         return $this->getCSVOutputTemplateResponse();
    //     }

    //     if ($outputTemplateType === 'EXCEL') {
    //         return $this->getOutputExcelTemplateResponse();
    //     }

    //     if ($outputTemplateType === 'GRIDFORM') {
    //         return $this->getOutputGridformTemplateResponse();
    //     }

    //     return $this->handleError();

    // }


    // Stuff that we are going to handle when creating a default form for all content which is going to be held in a gridform
    /*

    // $request = $this->getRequest();
    // $data = json_decode($request->getContent(), true);
    // $totalOutputSamples = $data['totalOutputSamples'];
    // $outputSampleDefaults = $data['outputSampleDefaults'];

    // if ($outputSampleDefaults == null ) {
        // $outputSampleDefaults = [];
    // }

    if (!$this->isMultiDimArray($outputSampleDefaults)) {
        $temp = array();
        for ($i =0; $i < $totalOutputSamples; $i++) {
            $temp[] = $outputSampleDefaults;
        }
        $outputSampleDefaults = $temp;
    }

        */

    // Trash
    /*

if (array_key_exists('outputSampleType', $data)) {
            $outputSampleTypeId = $data['outputSampleType']['id'];
        } else {
            $outputSampleTypeId = 1;
        }

    */

// Need to work on stripping this down a little bit more to get it all working
    private function getOutputExcelTemplateResponse($gridContents, $importer)
    {
        // Testing

        $path = '\AppBundle\Entity\Storage\Sample';

        // $test = new $path;
        // $test->setId(1);
        // echo $test->getId();

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


        // Testing portion
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

} // end of class

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
use AppBundle\Entity\Storage\WorkingSet;

class BaseWorkingSetController extends CarbonApiController
{
    /**
     * @var string The namespace of the resource entity
     */
    const RESOURCE_ENTITY = "AppBundle\Entity\Storage\WorkingSet";

    /**
     * @var string The form type for this resource
     */
    const FORM_TYPE = "working_set";

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
        //Should have a way of doing this without a put request to samples


    // Add a delete option here
        //Want to have a way of removing samples from the working set without a put request to sample.


    // Also need to add a form type.
        //Should have a form type which lets you post directly to this table as you would with equipment status detail.

    /**
     * Handles the HTTP POST request for the group entity
     *
     * @Route("/storage/working-set", name="working_set_post")
     * @Method("POST")
     *
     * @return Response
     */
    public function handlePost()
    {
        return parent::handlePost();
    }

    /**
    * Handles the HTTP POST request to add a sample to a workingset
    *
    * @Route("/storage/working-set-add-id/{uid}/{sid}", name="working_set_add_post_id")
    * @Method("POST")
    *
    * @return Response
    */
    public function handlePostNoForm($uid, $sid)
    {
        $sampleRepo = $this->getEntityManager()->getRepository('AppBundle\Entity\Storage\Sample');
        $userRepo = $this->getEntityManager()->getRepository('Carbon\ApiBundle\Entity\User');

        $em = $this->getEntityManager();

        // Add this
        // If find by discovers that the entry already exists a new one should not be created.

        $wset = new WorkingSet();
        $wset->setSample($sampleRepo->find($sid));
        $wset->setUser($userRepo->find($uid));
        $em->persist($wset);

        $em->flush();

        $res = new Response('success', 200);
        return $res;

    }

    /**
    * Handles the HTTP POST request to add a sample to a workingset
    *
    * @Route("/storage/working-set-remove-id/{uid}", name="working_set_remove_post_id")
    * @Method("PUT")
    *
    * @return Response
    */
    public function handleDeleteNoForm($uid)
    {

        $em = $this->getEntityManager();
        $workingSet = $this->getEntityManager()->getRepository('AppBundle\Entity\Storage\WorkingSet');

        $request = $this->getRequest()->getContent();

        $num = 0;

        foreach (json_decode($request, true) as $object) {

            foreach($object as $obj){

                // echo $obj['id'];
                $wset = $workingSet->findBy(array('userId' => $uid, 'sampleId' => $obj['id']));

                if(count($wset) > 0 ){

                    foreach($wset as $workingEntity){
                        $num += 1;
                        $em->remove($workingEntity);
                    }

                }

            }

        }

        // if($num > 0){

            $em->flush();
            $res = new Response('worked', 200);
            return parent::handleMTMGet("user", $uid);
            // return $res;

        // }

        $res = new Response('Path expects a workingset entry to exist with the given params.', 401);
        return $res;

    }

    /**
    * Handles the HTTP POST request to add a sample to a workingset
    *
    * @Route("/storage/working-set-remove-all/{uid}", name="working_set_remove_all")
    * @Method("DELETE")
    *
    * @return Response
    */
    public function handleDeleteAll($uid)
    {

        //check what user it is...

        if($this->getUser()->getId() == $uid){
            $em = $this->getEntityManager();

            $workingSet = $this->getEntityManager()->getRepository('AppBundle\Entity\Storage\WorkingSet');
            $wset = $workingSet->findBy(array('userId' => $uid));

            if(count($wset) > 0 ){

                foreach($wset as $workingEntity){
                    $em->remove($workingEntity);
                }

                $em->flush();
                $res = new Response('worked', 200);
                return $res;
            }

            $res = new Response('Working set already empty -- could not find any entries which needed to be deleted.', 404);
            return $res;
        }

        $res = new Response('Unauthorized: You do not have permissions to alter the Working Set of another user.', 403);
        return $res;

    }


// Generate a bulk update file
    //Still want to add support to order the working set stuff. I took that out of the box export thing
    /**
     * Handles the HTTP GET request for creating a bulk update excel sheet for your working set.
     *
     * @Route("/storage/working-set-bulk/excelDownload", name="working_set_excel_download")
     * @Method("GET")
     *
     * @return Response
     */
    public function workingSetExcelDownload()
    {

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



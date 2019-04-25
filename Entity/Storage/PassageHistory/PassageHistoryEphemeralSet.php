<?php

namespace Carbon\ApiBundle\Entity\Storage\PassageHistory;

use Carbon\ApiBundle\Entity\Storage\PassageHistory\PassageHistoryOperatorInterface;
use Carbon\ApiBundle\Entity\Storage\PassageHistory\PassageHistoryMaterialInterface;
use Carbon\ApiBundle\Entity\Storage\PassageHistory\PassageHistoryEphemeral;

/*

    Written by Taylor Jones


    This entity should not be persisted in the database -- it's main purpose it to avoid cycles in passage histories
    It does this by having a series of other epehemeral entities in a relationship structure with a couple little helper functions that allow for a graph to be traversed


    buildBasicHistory:
        Will find the input samples of current request / sample -> find origins of those
        Only serializes what is immediately needed

        number of downstream ephemerals is not used here

    Not a factor in buildBasicHistory
        numberOfDownstreamEpehmerals - the number of samples whos passage histories are serielized when performing a more in depth construction of the graph.




    For internal use only




*/

class PassageHistoryEphemeralSet
{
    public function construct($em)
    {
        $this->em = $em;

    }

    public function construct($em, $sentinel)
    {
        $this->em = $em;
        $this->sentinel = $sentinel;

    }

    // Depth first or breadthfirst search

    // Serializaton group default
    /**
     *
     */
    protected $brokenByCycle = false;
    protected $brokenByDepth = false;

    protected $em; // The entity manager that is used to look up entity detail ids.

    //The base of the linked list -- where we start serializing
    protected $sentinel;

    public function getSentinel(){

        return $this->sentinel;

    }

    public function setSentinel($sentinel) {

        $this->sentinel = $sentinel

    }

    protected $visitedNodes; // Not Serialized
    protected $nodeVisitList; // Not serialized

    // This should be the number of children of a given stage should be included in the string
    protected $numberOfDownstreamEphemerals = 8;

    public function getNumberOfDownstreamEphemerals()
    {


    }

    public function setNumberOfDownstreamEphemerals($value)
    {

    }


// In this simple set of passage histories it is unlikely that we will ever exceed this value...
    protected $maxSerializationDepth = 15;

    public function getMaxSerializationDepth()
    {
        return $this->maxSerializationDepth;
    }

    public function setMaxSerializationDepth($maxDepth)
    {
        $this->maxSerializationDepth = $maxDepth;
        return $this;
    }

    public function addParent();
    public function addChild();

    // protected $goldenChild;
    // public function getGoldenChild();
    // public function setGoldenChild();


    //breadth first seach


    // Follows an entity back through history -- does not serialize the siblings of input sampeles -- just the input samples themselves
    public function buildBasicHistory($obj = null)
    {
        // If an object is provided use that object if not use the sentinel - if neither are provided throw an exception
        $nodeVisitList[] = $obj ? $obj : ($this->getSentinel() ? $this->getSentinel() : false);

        if (!$tempObj)
        {
            throw new \LogicException('PassageHistoryEphemeralSet: buildBasicHistory does not have a node to begin building a linked list from');
        }

        $entityDetails = array();
        $entityDetailRepo = $em->getRepository('Carbon\ApiBundle\Entity\EntityDetail');


        while (1) {

            // gettype
            if ($tmpObj instanceof PassageHistoryOperatorInterface) {

                foreach ($tmpObj->getInputMaterials() as $input) {

                    try {
                        $entDet = $entityDetails[" " . $tmpObj->getId()];

                    } catch (Exception $e) {
                        $entDet = $entityDetailRepo
                    }

                    array_key_exists(". " . $entityDetail->getId(), $entityDetails) ? true : ($entityDetails;

                    new PassageHistoryEphemeral();

                }

            }

            if ($tmpObj instanceof PassageHistoryMaterialInterface) {

                foreach ($tmpObj->getUpstreamTransformers() as $transformer) {

                    new passageHistoryEphemeral();

                }

            }

            break;

        }

    }

    public function buildCompleteHistory($obj, $breadth){


        // Get Input Materials
        // Get output materials


        // Get upstream transformers
        // Get downstream transformers


    }

// Helper functions
    protected function createIndex ($entDetId, $id)
    {
        return array(
            'entDet' => $entDetId,
            'id' => $id
        )
    }

}

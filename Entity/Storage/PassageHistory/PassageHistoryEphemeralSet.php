<?php

namespace Carbon\ApiBundle\Entity\Storage\PassageHistory;


use ...
use ...


/*
    Written by Taylor Jones


    This entity should not be persisted in the database -- it's main purpose it to avoid cycles in passage histories
    It does this by having a series of other epehemeral entities in a relationship structure with a couple little helper functions that allow for a graph to be traversed


*/

class PassageHistoryEphemeralSet
{

    // Depth first or breadthfirst search

    protected $brokenByCycle = false;

    protected $visitedNodes;
    protected $nodeVisitList;

// What node is the chosen one? -- the node that started it all
    protected $epoch;

    // This should be the number of children of a given stage should be included in the string
    protected $numberOfDownstreamEphemerals = 3;


    public function addParent();
    public function addChild();

    public function getEpoch();
    public function setEpoch();


    //breadth first seach


    // Follows an entity back through history -- does not serialize the siblings of input sampeles -- just the input samples themselves
    public function buildBasicHistory($obj)
    {

        // gettype
        if ($obj instanceof PassageHistoryOperatorInterface) {


        }



    }

    public function buildCompleteHistory($obj, $breadth){



    }

// Helper functions



}

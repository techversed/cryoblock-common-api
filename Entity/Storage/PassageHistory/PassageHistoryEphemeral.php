<?php

namespace Carbon\ApiBundle\Entity\Storage\PassageHistory;

/*

    These objects should never be persisted in the database.

    This is used to construct a representation of each a passage history in memory.

    Things become complicated when creating a graph of all of the objects in the passage history..

    This class is used in conjunction with PassageHistoryEphemeralSet in order to create a graph that represents a samples passage history.


*/

class PassageHisotryEphemeral
{

    public function __construct()
    {


    }

    public $validTypes = array("Operator", "Material");

    // Serialized
    protected $type;
    public function getType();
    public function setType($type);

    // Some sort of string that uniquely identifies this object
    protected $uniqueIdentifier;
    public function getUniqueIdenfier();
    public function setUniqueIdenfier();

    protected $entityDetail;
    public function getEntityDetail();
    public function setEntityDetail();

    protected $children = array();
    public function setChildren();
    public function getChildren();
    public function addChild();

    // The favorite child is used to group siblings together when working on the frontend.
    protected $goldenChild;
    public function getGoldenChild();
    public function setGoldenChild();

    protected $parents = array();
    public function getParents();
    public function setParents();
    public function addParent();

    // Boolean that tells if it is worthwhile to continue down this path of serialization.
    protected $ofInterest;
    public function getOfInterest();
    public function setOfInterest();

    protected $lowestOrderedSibbling; // there needs to be a way of ordering all of the ephemerals at a given stage to determine where they belong in the order

    // ENtities should all have a  single creating request -- we don't have to worry about pluralities when we are going upstream on operators.
    // When we are going downstream on operators we are not going to go very many stops past what is needed.


    // This first version should just follow the path upstream and we can worry about the rest later on.



    // in each grouping we are going to be limited to single type of entity so we should be able to just use the regular id of that tentity
    // That actually won't work... There can be multiple types of requests that take the same input

// unique idenfier

// type

// upstream

// downstream
    // returns the full list of things which are downstream of the given element;

    //other things to implement
'

}

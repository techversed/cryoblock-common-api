<?php

namespace Carbon\ApiBundle\Integration\AutomatedFreezer;



abstract class BaseAutomatedFreezerDivisionSet
{

    // 'const LOCALVIEW' - must be defined in class files which extend this abstract class.
        // LOCALVIEW is a boolean which determines if Utilties maintains a local copy of what the automated freezer's division sets -- If it does not it will be foreced to query the device every time it wants to gain an understanding of what the freezer's view of its inventory is said to be.

    // 'const LOCALCACHEREPOPATH' -- string which points to the repository path which Doctrine entity manager has associated with the given type
        // Must be provided if LOCALVIEW is true

    // asdfasdfasdf
    abstract function getAll();

    // asdfasdfasdf
    abstract function findDescrepancies();

    // asdfasdfasdf
    abstract function returnUtilitiesIdentifiers();

    // asdfasdfasdf





    // Check to ensure that everything needed by these methods is present in the classes files which extend this.
    public function checkRequirements()
    {
        if(!defined('static::LOCALVIEW')){
            return false;
        }

    }

}

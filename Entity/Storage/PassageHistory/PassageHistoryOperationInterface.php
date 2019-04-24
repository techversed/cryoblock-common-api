<?php

namespace Carbon\ApiBundle\Entity\Storage\PassageHistory;

/*

    BaseRequest should implement this

    A transformer is anything that takes an input and/or creates an output in the life of a sample/sequence.

*/

interface PassageHistoryOperationInterface
{


    /*
        returns
            array(
                object_class_name => iterateable array / array collection of all objects of the given type that are inputs
                object_class_name_2 => ''
            )
    */
    public function getInputMaterials();

    /*
        This should be exactly the same layout as the getInputs funciton
    */
    public function getOutputMaterials();

}

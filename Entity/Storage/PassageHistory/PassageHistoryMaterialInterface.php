<?php

namespace Carbon\ApiBundle\Entity\Storage\PassageHistory;

/*

    Everything that exists in a passage history should implement this. Should Implement this


    A passage history entity is anything that

*/

interface PassageHistoryMaterialInterface
{

    /*

        This should return an array with classtypes linked with objects of that type which are inputs to other things

    */
    public function getDownstreamTransformers();





    /*


    */
    public function getUpstreamTransformers();

    public function getEntityDetailId();

}


/*
SCRATCH WORK

TEMPORARY CLASS WHICH IS ONLY USED IN THE CREATION OF THE PASSAGE HISTORY AND IS NOT ACTUALLY PERSISTED IN THE DATABASE.


protected function build history.(entity detail  id, id)
{

    getMaterial



    if implements material
        get Upstream



    if implements operation


}












*/

<?php

namespace Carbon\ApiBundle\Entity\Storage\PassageHistory;

/*

    This interface will be implemented by anything that links a Operation (request) with a Material (sequence/ sample/ whatever the hell)


*/

interface PassageHistoryOpMatLinkerInterface
{

    public function getOperation();

    public function getMaterial();

}

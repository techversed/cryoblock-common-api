/*



*/

<?php

//This will need to be changed
namespace AppBundle\Service\Integration\Device\Storage;


use AppBundle\Service\Integration\Device\Storage;




/* FILE DESCRIPTION AND BACKGROUND INFORMATION ON THIS FILE.

    Written by Taylor Jones.

    The purpose of this file is to wrap functionality from the Brooks Biostore III Cryo Application Programming Interface into small easily callable functions that can easily be used throughout the app.

    Biostore III and Biostore III Cryo will both use the same API have the same API.

    There are three types of functions contained in this file.

        Interface functionality - functionality which interacts directly with Utilities and offers a generic way of implementing common functionality across multiple devices - all automated freezers can would implement all (in a perfect world) interface functions which will allow for Utilities to handle each freezer without knowledge of its specific implmentation. Functions which have access to information in Utilities should be Interface functions.

        Helper functionality - used to automate tasks which are not simply API calls - not directly called by utilities & not directly making calls to the API

        Wrapper functionality - This functionality is designed to simplify the tasks of making API calls - wrapper functions will typically be one to one with functions in the API and will handle request formatting, path, and port ...etc information such that the programmer will not need to worry abotu any of that with individual calls

*/

//THis will need to be changed
// This class should serve as a wrapper for all Biostore III and Biostore III Cryo devices that are purchased by the lab.
class BrooksBiostoreIIIApi implements AutomatedFreezer
{

    //set up curl and hit

    public function fetchDivision($id = array(), $user)
    {

    }

    public function storeDivision($id = array(), $user())
    {

    }

    // Send a request ot the system for
    public function requestSamples($ids = array(), $user)
    {

    }

    // This may also involve
    public function placeSamples($ids = array(), $user)
    {

    }

// Interface functionality -- Only functions that should be called directly by Utilities

    // Chain all of the requests together which are needed to create an input order request to the Biostore III
        // Arguments
            // $ip - should be the ip or hostname of the device that you wish to send the request to
            // $divisionId - an array of ids for all of the divisions and samples that you would like to cran array of ids for all of the divisions and samples that you would like to create
            // $username - The username of the user in Utilities that the order should be created for -- operates under the assumption that a new user has been created in the brooks with the same username as the Utilities User
    function inputDivisionChain($ip, $divisionId = array(), $username)
    {

        // Obtain an access token
        $accessToken = obtainAccessToken($ip);

        // Look up the user for the order
        $userId = getUserId($ip, $accessToken, $username);

        // Create the Master file


        // Submit the request with the Master File that can

        return $userId;
    }


    // Perform all of the actions necessary for a

    function outputDivisionIdsChain($ip, $divisionIds = array(), $sampleIds = array(), $username)
    {

        // Obtain an access token
        $accessToken = obtainAccessToken($ip);

        // Look up the user for the order
        $userId = getUserId($ip, $accessToken, $username);

        // Create a list of the samples that should be part of this order.
        $listId = createList($ip, $accessToken, $ids, $attempts=0);

        // Pass that list on to the create output order function

        return true;
    }

// Helper functionality

    // Places the layout of a given Utilities division into a "Master file" that can be passed to the Brooks system in order to ensure that the system has an understanding of how the box is set up.
        // Provide it with the id number of the division that needs to be put into Utilities
    function formatInputBoxLayout($divisionId)
    {
        $temp = true;

        // Parent Rack
        // Parent Shelf
        // Box ID
        // Tube ID
        // Tube Position

        return $temp;
    }

    function formatOutputBoxLayout($divisionId)
    {

        $temp = true;

        // Item Type
        // Box entry type
        // Box ID
        // Rack
        // Shelf
        // Tube Entry Type
        // Tube ID
        // Tube Position
        // Comment

        return $temp;
    }

// Wrapper functionality

    // Send a request to the Brooks Biostore III in order to obtain a accessToken.
        // $ip requires the ip address or host name where the device can be found.
        // $client_id should be the id that the biostore uses to verify the device that is trying to connect to the Biostore's API - By default it is assumed to be the only id that we have currently -- May differ across different biostore devices.
        // $client_secret is essentailly a password which is used in conjunction with the id to identify the hose that is making the request -- as a result of the Brooks implementaiton of oauth2 there is one secret per host instead of being on a per client basis.
    function obtainAccessToken ($ip, $client_id = 'b3capiclient', $client_secret = '4E7EAEF9-7A2F-4E9C-A647-1196823E5EA6')
    {

        // Variables held constant across all requests of this type
        $prefix = 'https://';
        $port = '44333';
        $path = '/core/connect/token';
        $username = 'admin';
        $password = 'Brooks123';
        $scope = 'b3c-ws-api offline_access';
        $grant_type = 'password';

        $fullUrl = $prefix.$ip.':'.$port.$path;

        // Params in the body of this post request -- encoded as a string
        $params = array(
            'grant_type' => $grant_type,
            'username' => $username,
            'password' => $password,
            'scope' => $scope,
            'client_secret' => $client_secret,
            'client_id' => $client_id
        );

        $urlEncodedParams = http_build_query($params);

        // Curl options definition
        $curlOptions = array(
            CURLOPT_URL => $fullUrl,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $urlEncodedParams,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_RETURNTRANSFER => true
        );

        $ch = curl_init();
        curl_setopt_array($ch, $curlOptions);
        return json_decode(curl_exec($ch), true)['access_token'];

    }

    // Get the history of all of the orders which have taken place in the given Biostore III
        // $ip should be the IP address or host name of the device that you are trying to connect to
        // $accessToken should be the accessToken that is provided to the client via the obtainAccessToken function
    function getOrderHistory($ip, $accessToken)
    {

        $prefix = 'https://';
        $port = '45000';
        $path = '/ws/orders/history';

        $fullUrl = $prefix.$ip.':'.$port.$path;

        $httpHeader = array(
            'authorization: Bearer '.$accessToken,
            'cache-control: no-cache',
        );

        $curlOptions = array(
            CURLOPT_URL => $fullUrl,
            CURLOPT_HTTPHEADER => $httpHeader,
            CURLOPT_HTTPGET => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_RETURNTRANSFER => true
        );

        $ch = curl_init();
        curl_setopt_array($ch, $curlOptions);
        return curl_exec($ch);

    }

    // Create a list in the brooks system from a given set of ids return the list object that is in the system's response.
        // Arguments
            // $ip should be the IP address or hostname that we are going to be sending the request to
            // $accessToken should be the token which as been obtained from the server prior to the execution of this function
            // $ids should be a list of ids that should be included in the list that we are creating
            // $attempts is used in the event that the call to the other system fails. -- I have set this function to recursively call itself in the event of failure -- The most likely failure is that a list with the given name already exists - waiting one second should generate a new list name since the datetime will be changing -- I don't want to have a random component of the list name but that may need to happen if the volume of lists created reaches a point where collisisions are too common.
        // return value:
            // Id of created list if successful
            // -1 if unsuccessful
    function createList($ip, $accessToken, $ids, $attempts = 0)
    {

        if ($attempts >= 5){
            return -1;
        }

        $prefix = 'https://';
        $port = '45000';
        $path = '/ws/inventory/itemLists';

        $fullUrl = $prefix.$ip.':'.$port.$path;

        $httpHeader = array(
            'authorization: Bearer '.$accessToken,
            'cache-control: no-cache',
        );

        // build body of request
        // Need to generate a new unique name for each list that we are going to create
        // date('Y-m-d H:i:s');
        $currentTime = new \DateTime();
        $currentTimeString = $currentTime->format('YmdHis');
        $postPendValue = (string) rand(1,10000);

        $samples = array();
        foreach($ids as $id){
            $samples[] = array('inventoryItemId' => $id);
        }

        $data = array(
            'name' => 'Utilities Order'.$currentTimeString.$postPendValue,
            'items' => $samples
        );

        $urlEncodedString = http_build_query($data);

        $curlOptions = array(
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_URL => $fullUrl,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => true,
            CURLOPT_POSTFIELDS => $urlEncodedString,
            CURLOPT_HTTPHEADER => $httpHeader,
            CURLOPT_RETURNTRANSFER => true
        );

        $ch = curl_init();
        curl_setopt_array($ch, $curlOptions);
        $retValue =  curl_exec($ch);

        $decoded = json_decode($retValue, true);

        if (!array_key_exists('error', $decoded)) { // change this later to actually perform error checking.

            return $decoded['id'];

        }

        else {

            sleep(1);
            return createList($ip, $accessToken, $ids, $attempts+1);

        }

    }

    // Untested
    // Creates an output order for a list of items (created before this is called) to be fulfilled on behafl of a user (specified with userId -- userId lookup the their Utilities username and use the id that the Brooks returns)
        // $ip should be the the IP address of hostname of the specific device that you would like to send the request to.
        // $listId should be the id value of the list that you created to use for this order -- can be created by calling the createList function
    function createOutputOrder($ip, $accessToken, $listId, $userId)
    {

        $prefix = 'https://';
        $port = '45000';
        $path = '/ws/orders/CreateOutputOrder'; // Fill this out later

        $fullUrl = $ip.$prefix.':'.$port.$path;

        $data = array(
            'order' => array(
                'name' => 'Utilities Order',
                'orderType' => 'Output',
                'priority' => 83,
                'state' => 'Paused',
                'userName' => 'Admin',
                'properties' => array(
                    array(
                        'name'=> 'InventoryItemListId',
                        'propertyType' => 'String',
                        'value' => $listId
                    )
                )
            )
        );

        $httpHeader = array(
            'authorization: Bearer '.$accessToken,
            'cache-control: no-cache',
            'Content-type: application/json'
        );

        $curlOptions = array(
            CURLOPT_URL => $fullUrl,
            CURLOPT_POST => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS => $jsonBody,
            CURLOPT_HTTPHEADER => $httpHeader
        );

        $jsonBody = json_encode($data, JSON_PRETTY_PRINT);

        $ch = curl_init();
        curl_setopt_array($ch, $curlOptions);
        return curl_exec($ch);

    }

    // Untested
    // Creates an input order from a division with sample information already filled out
        // $ip should be the IP or hostname of the device that you would like to connect to
        // $accessToken should be the access token provided to Utilities by the Biostore III - by calling the obtainAccessToken() function in this file.
        // $userId -- The id assigned to the user by the BioStore III
    function createInputOrder($ip, $accessToken, $userId, $masterFile = null)
    {

        $prefix = 'https://';
        $port = '45000';
        $path = '/ws/orders/CreateOutputOrder'; // Fill this out later

        $fullUrl = $prefix.$ip.':'.$port.$path;

        $httpHeader = array(
            'authorization: Bearer '.$accessToken,
            'cache-control: no-cache',
            'Content-type: application/json'
        );

        //need to add a section where we are creating the body of the post reqeust
        $data = array(
            //a;
        );

        $jsonBody = json_encode($data, JSON_PRETTY_PRINT);

        $curlOptions = array(
            CURLOPT_URL => $fullUrl,
            CURLOPT_POST => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_POSTFIELDS => $jsonBody,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $httpHeader
        );

        $ch = curl_init();
        curl_setopt_array($ch, $curlOptions);
        return curl_exec($ch);

    }

    // Find the id that is associated with a given user. Operates under the assumption that all users have the same username in Brooks as in Utilities.
        // Arguments:
            // $ip is the IP address or hostname of the destination API
            // $accessToken the access token provided to Utilities by the Biostoer III - obtained through a call to the obtainAccessToken() funciton in this same file.
            // $username - the username that we aree going to query for
        // Returns:
            // id of user
            // -1 if no user exists in the Brooks system with the given username.
    function getUserId($ip, $accessToken, $username)
    {
        $path = '/ws/auth/users';
        $port = '45000';
        $prefix = 'https://';

        $fullUrl = $prefix.$ip.':'.$port.$path;

        $httpHeader = array(
            'authorization: Bearer '.$accessToken,
            'cache-control: no-cache',
        );

        $curlOptions = array(
            CURLOPT_HTTPHEADER => $httpHeader,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HTTPGET => true,
            CURLOPT_URL => $fullUrl
        );

        $ch = curl_init();
        curl_setopt_array($ch, $curlOptions);
        $users = curl_exec($ch);

        $users = json_decode($users, true);

        foreach($users['value'] as $user){
            if($user['userName'] == $username){
                return $user['id'];
            }
        }

        return -1; // If the function has not returned by this point then the user does not exist in the Brooks system.

    }

    // Obtain a list of every item in the given Biostore III system.
        //Arguments:
            // $ip - the IP address of hostname of the device that you should send the request to.
            // $accessToken -- the accessToken provided to Utilities by a prior call to obtainAccessToken() function in this file.
        //Returns:
            // Returns an array of the json_decoded output from the getInventoryItems call.
    function getInventoryItems($ip, $accessToken)
    {

        $path = '/ws/inventory/items';
        $prefix = 'https://';
        $port = '45000';

        $fullUrl = $prefix.$ip.':'.$port.$path;

        $httpHeader = array(
            'authorization: Bearer '.$accessToken,
            'cache-control: no-cache',
        );

        $curlOptions = array(
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $httpHeader,
            CURLOPT_HTTPGET => true,
            CURLOPT_URL => $fullUrl
        );

        $ch = curl_init();
        curl_setopt_array($ch, $curlOptions);
        return curl_exec($ch);

    }

    // Obtain a list of every item list which exists in the given Biostore III system.
        // $ip - the IP address or hostname of the device that the request should be sent to
        // $accessToken -- the access token provided to Utilities through previous API call made with the obtainAcccssToken() function in this file.
    function getInventoryItemLists($ip, $accessToken)
    {

        $prefix = 'https://';
        $port = '45000';
        $path = '/ws/inventory/itemLists';

        $fullUrl = $prefix.$ip.':'.$port.$path;

        $httpHeader = array(
            'authorization: Bearer '.$accessToken,
            'cache-control: no-cache'
        );

        $curlOptions = array(
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $httpHeader,
            CURLOPT_HTTPGET => true,
            CURLOPT_URL => $fullUrl
        );

        $ch = curl_init();
        curl_setopt_array($ch, $curlOptions);
        return curl_exec($ch);

    }

    // Get a list of all of the labware types that are registered with the brooks system.
        // $ip is the ip address or hostname of the system that you would like to send a request to.
        // $accessToken is the accessToken which has been acquired prior to this point -- call to obtainAccessToken function.
    function getAllLabwareTypes($ip, $accessToken){

        $prefix = 'https://';
        $path = '/ws/inventory/labwareTypes';
        $port = '45000';

        $fullUrl = $prefix.$ip.':'.$port.$path;

        $httpHeader = array(
            'authorization: Bearer '.$accessToken,
            'cache-control: no-cache'
        );

        $curlOptions = array(
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $httpHeader,
            CURLOPT_HTTPGET => true,
            CURLOPT_URL => $fullUrl
        );

        $ch = curl_init();
        curl_setopt_array($ch, $curlOptions);
        return curl_exec($ch);

    }

    // We will just use a new access token for each transaction since the timeout window on a token is only 20 minutes and there is no option to refresh an expired token.
    // public function renewSession($username, $password, $client_secret, $client_id)
    // {
    //     $grant_type="password";
    //     $scope = "b3c-ws-api"; // I think that this may differ between cryo and -80

    //     $client_id = "b3capiclient";
    //     $client_secret = "4E7EAEF9-7A2F-4E9C-A647-1196823E5EA6";
    // }


}


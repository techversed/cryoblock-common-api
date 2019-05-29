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
    We would like for grid forms to be powerful enough to create and update objects in the same action -- as a result we are going to need to make it so that


    At the current point in time we are not making it so that anything can be sent to these routes without a user being logged in first -- we have built code to make it so that users that are not logged in yet are assumed to be the utilities-services user.

*/



class GridFormController extends CarbonApiController
{

    /*

        return new Response($data, $status, array(
            'Content-Type' => 'application/json',
        ));

        throw new UnauthorizedHttpException($message);

    */

    // I am pretty sure that we are really just going to be using post and put requests in order to do this.
    /**
     * @Route("/grid-form", name="grid_form_get")
     * @Method("GET")
     * @Security("has_role('ROLE_USER')")
     *
     * @return Response
     */
    public function getFormSet() {


        // Check permissions
        // Throw an exception if they should not be allowed to access this route -- this will really need to be decided on entity detail instead of being decided here...

        // This could be used in order to restore where someone left off -- this could allow for saving of the grid form and restoring -- this is not built as of yet but it will be used in the future

        // This is not really going to be used at this time... it should probably just return a well worded error

        $data = "200 OK";
        $status = 200;

        return new Response($data, $status, array(
            'Content-Type' => 'application/json',
        ));

    }

    // This function should call the script that persists all of the changes in the database when a post request comes in.
    /**
     * @Route("/grid-form/complete", name="grid_form_post")
     * @Method("POST")
     *
     * @return Response
     */
    public function postActionComplete()
    {

        // Check permissions
        // Throw an exception if they should not be allowed to access this route -- this should really be stored on entity detail somehow -- will need to add additional fields

        // $thing = 1;

        // if ($thing)

        return $this->handlePutPostComplete();

    }


    // This function should call the function that persists all of the changes in the database when a put request comes in.
    /**
     * @Route("/grid-form/complete", name="grid_form_put")
     * @Method("PUT")
     *
     * @return Response
     */
    public function putActionComplete()
    {

        // Check permissions
        // Throw an exception if they should have be allowded to access this route -- this should really be stored on entity detail..
        // Put and post are really just going to call the same function ()

        // $thing = 1;

        // if ($thing)

        return $this->handlPutPostComplete();

    }

    /**
     * @Route("/grid-form/validate", name="grid_form_validate_put")
     * @Method("PUT")
     *
     * @return Response
     */
    public function putActionValidate()
    {

        return $this->handlePutPostValidate();

    }

    /**
     * @Route("/grid-form/validate", name="grid_form_validate_post")
     * @Method("POST")
     *
     * @return Response
     */
    public function postActionValidate()
    {

        return $this->handlePutPostValidate();

    }

    /*
        The following portion may not make it into production
        I am writing the gridform class with the assumption that we may at some point want to have people that are not users posting to gridforms at times...
        I don't know if we are actually ever going to allow users to do this but we might as well support it...

        I don't know how are going to handle this going forwards --
    */

    /*
        End of the portion that may not make it into production
    */



    // This portion is not built yet.
    // We would like for ids to both be created and updated in the same action so we are going to need to
    //
    protected function handlePutPostValidate()
    {
        // There should be an additional property in here that asks whether it should have the shit sent back in the form of one of the frontend grids that are in the produciton controller.

        $em = $this->container->get('doctrine.orm.default_entity_manager');
        $usersRepo = $em->getRepository('Carbon\ApiBundle\Entity\User');

        // echo $em ? "yep" : "nah";

        // die();
        // We might as well check permissions here instead of setting it in the post and the put request place.

        $data = "200 OK";
        // get the user -- could add another check to see if they are hitting a route that is not in security.yml

        $user = $this->getUser();
        $userLoggedIn = $user ? true : false; // Bool to prepare for user not being logged in;
        $authorized = false;

        if ($userLoggedIn) {

            // Check if the user has the desired permission
            // FIX LATER -- for this stage in testing we are just going to assume that any logged in user has permissions to accesss any route -- this is going to be set on entity detail further down the line...


            // check roles here once we are done with the testing portion of this controller development
            // foreach(...){

                $authorized = true;


            // }
        }
        else {

            // UPGRADE LATER -- in the short term we are just going to disallow the use of anon routes on grid forms...
            // If the user is not logged in then we should check of anon users are allowd to access the route
            // This will be done on entity detail at some point

            // this route is going to be changed so that users are all allowed to
            if (false) {

                $authorized = true;
                $user = $usersRepo->find(212); // if they are not logged in assume that they really want the utilities service account -- blame anything on them;

                // echo "in the else";
            }

        }

        if (!$authorized) {

            throw new UnauthorizedHttpException("You don't have permission");

        }

        // if user not logged in;
        //$authorized = true


        // if user logged in
        // loop over groups and roles

        $data = $user->getId();

        $status = 200;

        return new Response($data, $status, array(
            'Content-Type' => 'application/json',
        ));

    }


    // The flow of this complete action is going to have all of the main permissions updated


    // Might also give the option to look up other fields in the event that an id is not given
    /*
        This funciton is designed to take an object provided on the frontend, locate the
        $em should be an entity manager
        $object should be whatever you are looking to add to the frontend
    */
    protected function lookupObject($em, $obj)
    {

        // Check if there is an entity detail id in the array.
        // If so look it up -- if not specified throw an exception
            // If lookup failes throw an exception
        if(!array_key_exists('entityDetailId', $obj))
        {

            throw new \Exception("there is no entity detail id provided in the object that you are looking up");

        }

        $entDet = $em->getRepository('Carbon\ApiBundle\Entity\EntityDetail')->find($obj['entityDetailId']);

        if (!$entDet) {

            throw new \Exception("There is no entity detail entry with the specified id number");

        }

        // Use the metadata stored on entity detail id in order to look up the object--
            // If id is not given throw an exception
            // if the object is not found then throw an exception

        $objRepo = $em->getRepository($entDet->getObjectClassName());

        if (array_key_exists('id', $obj)){

            $foo =  $objRepo->find($obj['id']);
            // $foo =  $objRepo->find(-1);

            // If the object lookup failed throw an exception
            if (!$foo) {

                throw new \Exception("Thre is no entity detail with the specified id");

            }

        }
        else {

            /*
                LONG TERM WE WOULD LIKE FOR THIS TO CREATE OBJECTS WHICH DO NOT EXIST YET -- IN THE SHORT TERM WE ARE JUST GOING TO THROW AN EXCEPTION IN THIS CASE
            */

            // $obj = new $entDet->getObjectClassName();
            // We should finish writing this so that the entity will be created here in the event that it does not already exists

            // $em->persist ...

            // For now we are just going to throw an exception reminding us of what we need to finish building

            throw new \Exception("Need to finish buidling the lookupObject function in GridFormController.php in common");

        }

        // $lookedUpObject = array();

        return $lookedUpObject;

    }

    /*
        This function takes an object which has been received from the entity manager and returns an array that conatains just the id
        This type of input is needed to hand things off to forms. The OTO transformer which is present in forms is set up to only need this information ...

    */
    protected function prepForOTO($obj)
    {
        return array('id' => $obj->getId());
    }


    // We are not done building this yet.
    // We would like for ids to both be created and updated in the same action so we are going to need to
    // Production controllers complete action was not even creating the samples -- it was an earlier request within the pipeline.
    protected function handlePutPostComplete()
    {

        // We may not even need to have the depleted all inputs handled on the backend like we are in production controller -- we could just use the gridform class in order to do all of this.

        $em = $this->container->get('doctrine.orm.default_entity_manager');
        $request = $this->getRequest();
        $data = json_decode($request->getContent(), true);

        return $this->lookupObject($em, array("entityDetailId" => "2", 'id' => '1'));

        // THIS IS ONLY INTENDED TO BE USED WITH THE TEST CASES -- IT SHOULD NOT MAKE IT INTO PRODUCTION
        // This is obviously not final -- this is just for testing purposes
        $storageContainer = $em->getRepository('AppBundle\Entity\Storage\StorageContainer')->findBy(array('name' => "Vial"))[0];
        $storageContainer = array('id' =>$storageContainer->getId());

        $sampleType = $em->getRepository('AppBundle\Entity\Storage\SampleType')->findBy(array('name' => "Primary Cells"))[0];
        $sampleType = array('id' =>$sampleType->getId());




        /*
            THIS PORTION IS GOING TO
        */





        /*
            END OF THE PARSING PORTION
        */

        // for this first version we are going to cook up some sample data and use that instead of passing it with the request

        // This is the structure of the data in the post request that we are going to be taking in.
        // The top level is going to be an array of entries
            // The second level is going to have properties for that entry.
            // It is going to have metadata which are the properites which would normally be present within the form for the object
            // It is also going to have a series of gridforms which are going to allow for additional metadata properties to be stored on the linker table entries for grid forms.

        // The metdata on linkertable use case
        // This should have id added to it later on.
        $exampleData1 = array(
            'actionType' => 'mtmParent',
            'Entities' => array(
                array(
                    'BaseMetadata' => array(),
                    'GridForms' => array(
                        array(
                            'columns' => array(),
                            'entries' => array(
                                'association1' => array(
                                    array('field1' => 'value1','field2' => 'value1','field3' => 'value1'),
                                    array('field1' => 'value1','field2' => 'value1','field3' => 'value1'),
                                    array('field1' => 'value1','field2' => 'value1','field3' => 'value1'),
                                    array('field1' => 'value1','field2' => 'value1','field3' => 'value1')
                                ),
                                'association2' => array(
                                    array('field1' => 'value1','field2' => 'value1','field3' => 'value1'),
                                    array('field1' => 'value1','field2' => 'value1','field3' => 'value1'),
                                    array('field1' => 'value1','field2' => 'value1','field3' => 'value1')
                                ),
                                'association3' => array(
                                    array('field1' => 'value1','field2' => 'value1','field3' => 'value1'),
                                    array('field1' => 'value1','field2' => 'value1','field3' => 'value1'),
                                    array('field1' => 'value1','field2' => 'value1','field3' => 'value1')
                                )
                            )
                        )
                    )
                )
            )
        );

        // The bulk update usecase
        // worth noting that the next level under grid forms is not really needed -- it is probably not necessary for us to separate out create, update ... etc but it would be a good idea for delete to be separate.

        // echo $storageContainer['id'];

        // print_r($storageContainer);
        // die();
        $exampleData2 = array(
            'actionType' => 'bulkEntity',
            'Entities' => array(
                'GridForms' => array(
                    'columns' => array(),
                    'entries' => array(
                        array('id' => 1, 'status' => 'Depleted','description' => 'this is the description','storageContainer' => $storageContainer, 'sampleType' => $sampleType)
                        // array('id' => 2, 'field1' => 'value1','field2' => 'value1','field3' => 'value1'),
                        // array('id' => 3, 'field1' => 'value1','field2' => 'value1','field3' => 'value1')
                    )
                    //,
                    // 'update' => array(

                    // ),
                    // 'delete' => array(

                    // )
                )
            )
        );

        // If both of the use cases above are accounted for then there should not be very many things that we are going to struggle to handle.

        // This array and this loop are only going to be used during the development stage of this pipeline.
        $examples = array($exampleData1, $exampleData2);


// In the next stage of testing we are going to move to running both testcases
        // foreach($examples as $example) {

        if(true) {

            // choose variable name other than example later on.
            $example = $examples[1]; // right now we are just going to worry about getting the regular bulk import working.

            if ($example['actionType'] == 'mtmParent') {

                // asdfasdfasd
                // Apply changes to the base entities

                // Create anything that does not exist already

                // Flush the entity manger

                // Create any mtms that do not exist
                // Persist them
                // Flush the entity manager

            }
            elseif ($example['actionType'] == 'bulkEntity') { // This is essentially going to be the same as the sample importer -- it is likely that we can change the sample importer to use this class intead of doing things the way that they were previously being done.

                // This is also going to need a series of transformers that are used when validating the submission

                // This still needs to be populated
                foreach ($example['Entities']['GridForms']['create'] as $element) {


                    // Assumes that the gridform has 3 properties called create, update, delete

                    // If the entity exists grab it,
                    // If it does not then create it. .

                    // Instead of handling this as was mentioned above it would probably be perfectly fine to just use form submission instead of wrigint all sorts of custom code to handle this....
                    // Sample importer should already handle things this way.

                    // Update and create can be handled in the same portion of this -- this would require us to have field names that line up with the ones that are used in the formtype.

                    // testing

                    // Set the classname -- this may be gotten from the entity detail or directly from the reqeust -- don't know how we want to handle this yet.
                    // There should probably be an array that stores all of the classnames...

                    $classname = 'AppBundle\Entity\Storage\Sample'; // This can be set in a number of different ways...

                    // at this point in time this has been set to evalute to false all of the time... change that before production ..

                    if (array_key_exists('id', $element)) {

                        // get the objectclassname from the entity detail entry thing
                        // $entity = find ...

                        $created = false;
                        $entity = $em->getRepository($classname)->find($element['id']);

                    }
                    else {

                        $created = true;
                        $entity = new $classname();

                        // entity = new ...

                    }

                    $form = $this->createForm('sample', $entity); // in the final version this should not be hardcoded...
                    $form->submit($element);

                    if(!$form->isValid()){

                        // This function is present witin CarbonApiController

                        // We should probably complete all of the actions and just make a list of the things that have errors instead of having the whole submisssion break in the event that one of them fails --
                        return $this->getFormErrorResponse($form);

                    }

                    if (array_key_exists('id', $element)) {

                        $em->persist($entity);

                    }

                }

                $em->flush();

            // end of bulkEntry code

            }
        }


        die();

        // Need to quickly validate that it would be possible to do bulk updates of non-nested data using this method...
            // Lets start out by assuming that we are going to need to use this in order to perform bulk updates on samples... what needs to be added?
            // We would also need to add a property at the top level asking if it is a mtm update or if it is a bulk update of a single type of entity.


        // possible values for the 'updateType' key.
            // mtmParent -- used when the user is trying to adjust the properties on a parent and would like to add metadata to a linker table entry (something that would not be possible using the regular formtype setup
            // bulkEntity -- If there is no parent object and you would like to use the gridform essentially as a regular bulk (like the excel upload) then this can be specified.

        // This complete action is going to function in basically the same way that production controller operates...


        foreach($entities as $entity)
        {

            // Here are all of the things

        }

        // $requestObjectFormData = $data['requestObject'];
        // $requestFormType = $data['requestFormType'];
        // $prodRequest = $em->getRepository($data['entity'])->find($data['id']);

        // This is not going to work yet

        echo "Made it to the end of the portion that is being built right now.";
        die();

        $usersRepo = $em->getRepository('Carbon\ApiBundle\Entity\User');
        // echo $em ? "yep" : "nah";
        // We might as well check permissions here instead of setting it in the post and the put request place.

        $data = "200 OK";
        // get the user -- could add another check to see if they are hitting a route that is not in security.yml

        $user = $this->getUser();
        $userLoggedIn = $user ? true : false; // Bool to prepare for user not being logged in;
        $authorized = false;

        if ($userLoggedIn) {

            // Check if the user has the desired permission
            // FIX LATER -- for this stage in testing we are just going to assume that any logged in user has permissions to accesss any route -- this is going to be set on entity detail further down the line...


            // check roles here once we are done with the testing portion of this controller development
            // foreach(...){

                $authorized = true;


            // }
        }
        else {

            // UPGRADE LATER -- in the short term we are just going to disallow the use of anon routes on grid forms...
            // If the user is not logged in then we should check of anon users are allowd to access the route
            // This will be done on entity detail at some point

            // this route is going to be changed so that users are all allowed to
            if (false) {

                $authorized = true;
                $user = $usersRepo->find(212); // if they are not logged in assume that they really want the utilities service account -- blame anything on them;

                // echo "in the else";
            }

        }

        if (!$authorized) {
            throw new UnauthorizedHttpException("You don't have permission");
        }

        // if user not logged in;
        //$authorized = true


        // if user logged in
        // loop over groups and roles

        $data = $user->getId();

        $status = 200;


        return new Response($data, $status, array(
            'Content-Type' => 'application/json',
        ));

    }

    // handle delete is not going to be implemented here
    // asdf
    // asdfl

}

// The format of the request should be as follows
/*

// The entity which is sent to this should be a list of entities which have a series of entities

// Grid forms should really only be used in order to set metadata on linker table entries.

// On this first round we are going to make things
    $entities = array(
        // Entity 1
        array(
            baseProperties = array()
            gridForms = array
                array(
                    'associationName' => array()
                )
            )
        ),
        // Entity 2
        array(
            baseProperties = array()
            gridForms = array(
                'associationName' => array()
            )
        )
    )
*/
// The above is outdated -- the new version of the standard is a comment in one of the functions above.


/*
LIST OF OTHER CHANGES THAT ARE GOING TO BE NEEDED IN ORDER FOR THIS TO ALL WORK OUT.
    Importers may need additioanl properties
    Everything that you would like to update with this is going to need to have its own importer created
    Entity detail needs to have the name of the form that needs to be grabbed.
    Entity detail is going to need to have the importer stored for each type of element that is allowed to be imported with this mechanism
    May need to have multiple different functions for bulk import -- not sure at this time if it makes more sense for us to have separate importers for bulk and linker updates or if it makes sense to add new functions to the importer depending upon which action you are attempting to make
    Entity detail should be expanded to have update, create, delete permissions roles on it -- since we are going to try to make something generic that is going to work with all type of entities it is going to be absolutely essential for us to have a mechanism to set this for individual entity types instead of setting a standard in the controller.


*/

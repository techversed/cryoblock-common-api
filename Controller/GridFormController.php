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

    // We are not done building this yet.
    // We would like for ids to both be created and updated in the same action so we are going to need to
    //
    protected function handlePutPostComplete()
    {


        // for this first version we are going to cook up some sample data and use that instead of passing it with the request

        // This is the structure of the data in the post request that we are going to be taking in.
        // The top level is going to be an array of entries
            // The second level is going to have properties for that entry.
            // It is going to have metadata which are the properites which would normally be present within the form for the object
            // It is also going to have a series of gridforms which are going to allow for additional metadata properties to be stored on the linker table entries for grid forms.
        $exampleData = array(
            'updateType' => 'mtmParent',
            'Entities' => array(
                array(
                    'BaseMetadata' => array(),
                    'GridForms' => array(
                        'association1' => array(
                            array(),
                            array(),
                            array()
                        ),
                        'association2' => array(
                            array(),
                            array(),
                            array()
                        );
                        'association3' => array(
                            array(),
                            array(),
                            array()
                        );
                    )
                )
            )
        );

        // Need to quickly validate that it would be possible to do bulk updates of non-nested data using this method...
            // Lets start out by assuming that we are going to need to use this in order to perform bulk updates on samples... what needs to be added?
            // We would also need to add a property at the top level asking if it is a mtm update or if it is a bulk update of a single type of entity.


        // possible values for the 'updateType' key.
            // mtmParent -- used when the user is trying to adjust the properties on a parent and would like to add metadata to a linker table entry (something that would not be possible using the regular formtype setup
            // bulkEntity -- If there is no parent object and you would like to use the gridform essentially as a regular bulk (like the excel upload) then this can be specified.




        $em = $this->container->get('doctrine.orm.default_entity_manager');

        // This complete action is going to function in basically the same way that production controller operates...
        $request = $this->getRequest();
        $data = json_decode($request->getContent(), true);


        $entities = $data['entities'];

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

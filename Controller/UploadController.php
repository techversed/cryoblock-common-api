<?php

namespace Carbon\ApiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UploadController extends Controller
{
    public function optionsAction()
    {
        $response = new Response();

        $data = array('success' => 'success');

        $response->setContent(json_encode($data, true));

        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
}

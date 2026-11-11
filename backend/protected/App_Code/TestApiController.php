<?php

class TestApiController extends ApiResourceController
{
    public function postJson($data)
    {

        return array(
            'debug' => 'TEST CONTROLLER REACHED',
            'received' => $data,
            'request_uri' => $_SERVER['REQUEST_URI'],
            'script_name' => $_SERVER['SCRIPT_NAME']
        );
    }
}

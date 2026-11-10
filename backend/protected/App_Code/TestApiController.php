<?php

class TestApiController extends ApiResourceController
{
    public function postJson($data)
    {
        return $data;
    }
}

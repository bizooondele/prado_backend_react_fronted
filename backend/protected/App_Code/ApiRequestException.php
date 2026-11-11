<?php

class ApiRequestException extends Exception
{
    public function __construct($statusCode, $message)
    {
        parent::__construct($message, $statusCode);
    }
}

<?php

abstract class ApiResourceController
{
    protected function requireString($data, $field)
    {
        if (!isset($data[$field]) || !is_string($data[$field]) || trim($data[$field]) === '') {
            throw new ApiRequestException(400, 'Missing or invalid field: ' . $field . '.');
        }

        return trim($data[$field]);
    }

    protected function requireId($data)
    {
        if (!isset($data['id']) || filter_var($data['id'], FILTER_VALIDATE_INT) === false) {
            throw new ApiRequestException(400, 'A valid id is required.');
        }

        return (int) $data['id'];
    }

    protected function requireRecord($record, $identifier)
    {
        if ($record === null || empty($record)) {
            throw new ApiRequestException(404, 'Record not found for: ' . $identifier . '.');
        }

        return $record;
    }

    protected function requireUserId($data)
    {
        if (!isset($data['user_id']) || filter_var($data['user_id'], FILTER_VALIDATE_INT) === false) {
            throw new ApiRequestException(400, 'A valid user_id is required.');
        }

        return (int) $data['user_id'];
    }

    protected function requireInt($data, $field)
    {
        if (!isset($data[$field]) || filter_var($data[$field], FILTER_VALIDATE_INT) === false) {
            throw new ApiRequestException(400, 'Missing or invalid field: ' . $field . '.');
        }

        return (int) $data[$field];
    }
}

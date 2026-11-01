<?php

class ChatHistoryApiController extends ApiResourceController
{

    public function getOne($data)
    {
        $from_user =  $this->requireString($data, 'username');
        $records = $this->requireRecord(ChatHistoryRecord::finder()->findAll('from_user = ? and not for_user = ?', $from_user  , $from_user), $from_user);
        return $records;
    }
}
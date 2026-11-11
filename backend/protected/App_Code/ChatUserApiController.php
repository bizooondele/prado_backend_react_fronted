<?php

class ChatUserApiController extends ApiResourceController
{
    public function getAll($data)
    {
        $users = array();
        ChatUserRecord::finder()->deleteAll('last_activity < ?', time()-300); //5 min inactivity
        foreach (ChatUserRecord::finder()->findAll() as $record) {
            $users[] = $this->toArray($record);
        }

        return $users;
    }

    public function getOne($data)
    {
        $username = $this->requireString($data, 'username');
        $record = $this->requireRecord(ChatUserRecord::finder()->findByPk($username), $username);

        return $this->toArray($record);
    }

    public function getLastActivity($data)
    {
        $username = $this->requireString($data, 'username');
        $record = $this->requireRecord(ChatUserRecord::finder()->findByPk($username), $username);

        return array(
            'username' => $record->username,
            'last_activity' => (int) $record->Last_Activity
        );
    }

    public function setLastActivity($data)
    {
        $username = $this->requireString($data, 'username');
        if (!isset($data['last_activity']) || filter_var($data['last_activity'], FILTER_VALIDATE_INT) === false) {
            throw new ApiRequestException(400, 'A valid last_activity value is required.');
        }

        $record = $this->requireRecord(ChatUserRecord::finder()->findByPk($username), $username);
        $record->Last_Activity = (int) $data['last_activity'];
        $record->save();

        return $this->toArray($record);
    }

    public function save($data)
    {
        $username = $this->requireString($data, 'username');
        if (strlen($username) > 20) {
            throw new ApiRequestException(400, 'username must not exceed 20 characters.');
        }

        if (ChatUserRecord::finder()->findByPk($username) !== null) {
            throw new ApiRequestException(409, 'Chat user already exists.');
        }

        if (isset($data['last_activity'])
            && filter_var($data['last_activity'], FILTER_VALIDATE_INT) === false) {
            throw new ApiRequestException(400, 'A valid last_activity value is required.');
        }

        $record = new ChatUserRecord();
        $record->username = $username;
        $record->Last_Activity = isset($data['last_activity'])
            ? (int) $data['last_activity']
            : time();
        $record->save();

        return $this->toArray($record);
    }

    public function removeAll($data)
    {
        $deleted = ChatUserRecord::finder()->deleteAll();
        return array('deleted' => (int) $deleted);
    }

    private function toArray($record)
    {
        return array(
            'username' => $record->username,
            'last_activity' => (int) $record->Last_Activity
        );
    }
}

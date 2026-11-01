<?php

class UserApiController extends ApiResourceController
{
    public function getAll($data)
    {
        $users = array();
        foreach (UserRecord::finder()->findAll() as $record) {
            $users[] = $this->toPublicUser($record);
        }

        return $users;
    }

    public function getAllWithComments($data)
    {
        $maxLength = null;
        if (isset($data['comment_length'])) {
            $maxLength = filter_var($data['comment_length'], FILTER_VALIDATE_INT, array('options' => array('min_range' => 1)));
            if ($maxLength === false) {
                throw new ApiRequestException(400, 'comment_length must be a positive integer.');
            }
        }

        $users = array();
        foreach (UserRecord::finder()->withComments()->findAll() as $record) {
            $users[] = $this->toPublicUserWithComments($record, $maxLength);
        }

        return $users;
    }

    public function getAllWithCommentsSQL($data)
    {
        $maxLength = null;
        if (isset($data['comment_length'])) {
            $maxLength = filter_var($data['comment_length'], FILTER_VALIDATE_INT, array('options' => array('min_range' => 1)));
            if ($maxLength === false) {
                throw new ApiRequestException(400, 'comment_length must be a positive integer.');
            }
        }

        $db = Prado::getApplication()->getModule('db')->DbConnection;
        $db->setActive(true);
        $sql = "SELECT u.id, u.name, u.email, u.status, c.id AS comment_id, SUBSTRING(c.comment, 1, :maxLength) AS comment, c.user_id FROM users u LEFT JOIN comments c ON u.id = c.user_id";
        $command = $db->createCommand($sql);
                $command->bindValue(':maxLength', $maxLength === null ? 2147483647 : $maxLength, PDO::PARAM_INT);
                $rows = $command->query()->readAll();
        $users = array();
        $userMap = array();
        foreach ($rows as $row) {
            $userId = $row['id'];
            if (!isset($userMap[$userId])) {
                $userMap[$userId] = array(
                    'id' => $row['id'],
                    'name' => $row['name'],
                    'email' => $row['email'],
                    'status' => $row['status'],
                    'comments' => array()
                );
            }
            if ($row['comment_id'] !== null) {
                $userMap[$userId]['comments'][] = array(
                    'id' => $row['comment_id'],
                    'comment' => $row['comment'],
                    'user_id' => $row['user_id']
                );
            }
        }
        $users = array_values($userMap);

        return $users;
    }

    public function getOne($data)
    {
        $id = $this->requireId($data);
        $record = $this->requireRecord(UserRecord::finder()->findByPk($id), $id);

        return $this->toPublicUser($record);
    }

    public function save($data)
    {
        $record = new UserRecord();
        $record->name = $this->requireString($data, 'name');
        $record->email = $this->requireEmail($data);
        $record->password = password_hash($this->requireString($data, 'password'), PASSWORD_DEFAULT);
        $record->save();

        return $this->toPublicUser($record);
    }

    public function update($data)
    {
        $id = $this->requireId($data);
        $record = $this->requireRecord(UserRecord::finder()->findByPk($id), $id);

        if (array_key_exists('name', $data)) {
            $record->name = $this->requireString($data, 'name');
        }
        if (array_key_exists('email', $data)) {
            $record->email = $this->requireEmail($data);
        }
        if (array_key_exists('password', $data)) {
            $record->password = password_hash($this->requireString($data, 'password'), PASSWORD_DEFAULT);
        }

        if (array_key_exists('status', $data)) {
            $record->status = $this->requireInt($data, 'status');
        }

        if (!array_key_exists('name', $data)
            && !array_key_exists('email', $data)
            && !array_key_exists('status', $data)
            && !array_key_exists('password', $data)) {
            throw new ApiRequestException(400, 'At least one field to update is required.');
        }

        $record->save();
        return $this->toPublicUser($record);
    }

    public function remove($data)
    {
        $id = $this->requireId($data);
        $record = $this->requireRecord(UserRecord::finder()->findByPk($id), $id);
        $record->delete();

        return array('id' => $id);
    }

    private function requireEmail($data)
    {
        $email = $this->requireString($data, 'email');
        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            throw new ApiRequestException(400, 'A valid email address is required.');
        }

        return $email;
    }

    private function toPublicUser($record)
    {
        return array(
            'id' => (int) $record->id,
            'name' => $record->name,
            'email' => $record->email,
            'status' => $record->status
        );
    }

    private function toPublicUserWithComments($record, $maxLength = null)
    {
        $user = $this->toPublicUser($record);
        $user['comments'] = array();
        foreach ($record->comments as $comment) {
            $user['comments'][] = array(
                'id' => (int) $comment->id,
                'comment' => $maxLength === null
                    ? $comment->comment
                    : mb_substr((string) $comment->comment, 0, $maxLength, 'UTF-8')
            );
        }

        return $user;
    }
}

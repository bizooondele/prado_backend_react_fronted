<?php

class CommentApiController extends ApiResourceController
{
    public function getAll($data)
    {
        $comments = array();
        foreach (CommentRecord::finder()->findAll() as $record) {
            $comments[] = $this->toPublicComment($record);
        }

        return $comments;
    }

    public function getOne($data)
    {
        $id = $this->requireId($data);
        $record = $this->requireRecord(CommentRecord::finder()->findByPk($id), $id);

        return $this->toPublicComment($record);
    }

    public function save($data)
    {
        $record = new CommentRecord();
        $record->user_id = $this->requireUserId($data);
        $record->comment = $this->requireString($data, 'comment');
        $record->save();

        return $this->toPublicComment($record);
    }

    public function update($data)
    {
        $id = $this->requireId($data);
        $record = $this->requireRecord(CommentRecord::finder()->findByPk($id), $id);

        if (array_key_exists('user_id', $data)) {
            $record->user_id = $this->requireUserId($data);
        }
        if (array_key_exists('comment', $data)) {
            $record->comment = $this->requireString($data, 'comment');
        }

        if (!array_key_exists('user_id', $data)
            && !array_key_exists('comment', $data)) {
            throw new ApiRequestException(400, 'At least one field to update is required.');
        }

        $record->save();
        return $this->toPublicComment($record);
    }

    public function remove($data)
    {
        $id = $this->requireId($data);
        $record = $this->requireRecord(CommentRecord::finder()->findByPk($id), $id);
        $record->delete();

        return array('id' => $id);
    } 
    private function toPublicComment($record)
    {
        return array(
            'id' => $record->id,
            'user_id' => $record->user_id,
            'comment' => $record->comment,
        );
    }
}
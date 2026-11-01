<?php

class ChatBufferApiController extends ApiResourceController
{

    public function getAllMessagesApi($data)
    {
        $messages = $this->requireRecord(ChatBufferRecord::finder()->getAllMessages(), "all messages");
        return array('messages' => $messages);
    }

    public function getUserMessagesApi($data)
    {
        $username = $this->requireString($data, 'username');
//        $messages = $this->requireRecord(ChatBufferRecord::finder()->getUserMessagesArray(array('username' => $username)), $username);
        $messages = ChatBufferRecord::finder()->getUserMessagesArray(array('username' => $username));
        return array('username' => $username, 'messages' => $messages);
    }

    public function getCreatedOn($data)
    {
        $id = $this->requireId($data);
        $record = $this->requireRecord(ChatBufferRecord::finder()->findByPk($id), $id);

        return array('id' => $id, 'created_on' => (int) $record->Created_On);
    }

    public function setCreatedOn($data)
    {
        $id = $this->requireId($data);
        if (!isset($data['created_on']) || filter_var($data['created_on'], FILTER_VALIDATE_INT) === false) {
            throw new ApiRequestException(400, 'A valid created_on value is required.');
        }

        $record = $this->requireRecord(ChatBufferRecord::finder()->findByPk($id), $id);
        $record->Created_On = (int) $data['created_on'];
        $record->save();

        return array('id' => $id, 'created_on' => (int) $record->Created_On);
    }

    public function saveMessage($data)
    {
        $record = new ChatBufferRecord();
        foreach(ChatUserRecord::finder()->findAll() as $user)
        {
            $message = new ChatBufferRecord();
            $message->for_user = $user->username;
            $message->from_user = $this->requireString($data, 'from_user');
            $message->message = $this->requireString($data, 'message');
            $message->save();
            $history = new ChatHistoryRecord();
            $history->saveMessage($message);
            
            if($user->username == $this->requireString($data, 'from_user'))
            {
                $user->last_activity = time(); //update the last activity;
                $user->save();
            }
        }
        //$record->from_user = $this->requireString($data, 'from_user');
        //$record->for_user = $this->requireString($data, 'for_user');
        //$record->message = $this->requireString($data, 'message');
        $record->saveMessage();

        return array(
            'from_user' => $record->from_user,
            'for_user' => $record->from_user,
            'message' => $record->message
        );
    }

    private function toPublicChatBuffer($record)
    {
        return array(
            'from_user' => $record->from_user,
            'for_user' => $record->for_user,
            'message' => $record->message,
            'created_on' => (int) $record->_created_on
        );
    }
}

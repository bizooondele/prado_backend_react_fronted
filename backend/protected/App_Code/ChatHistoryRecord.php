<?php

use Prado\Data\ActiveRecord\TActiveRecord;

class ChatHistoryRecord extends TActiveRecord
{
    const TABLE='chat_history';

    public $id;
    public $for_user;
    public $from_user;
    public $message;
    public $created_on;

    public static function finder($className=__CLASS__)
    {
        return parent::finder($className);
    }

    public function saveMessage(ChatBufferRecord $buffMessage)
    {
        $message = new self;
        $message->for_user = $buffMessage->for_user;
        $message->from_user = $buffMessage->from_user;
        $message->message = $buffMessage->message;
        $message->created_on = time();
        $message->save();
    }

    public function getUserMessagesArray($data)
    {
        $username = trim($data['username']);
        $messages = array();
        foreach($this->findAll('from_user = ? and not for_user = ?', $username  , $username) as $message) {
            $messages[] = array(
                'from_user' => $message->from_user,
                'message' => $message->message,
                'created_on' => (int) $message->created_on
            );
        }
        return $messages;
    }

    public function getAllMessages()
    {
        $messages = array();
        foreach($this->findAll() as $message) {
            $messages[] = array(
                'from_user' => $message->from_user,
                'for_user' => $message->for_user,
                'message' => $message->message,
                'created_on' => (int) $message->created_on
            );
        }
        return $messages;
    }

}
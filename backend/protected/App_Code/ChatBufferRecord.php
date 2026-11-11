<?php

use Prado\Data\ActiveRecord\TActiveRecord;

class ChatBufferRecord extends TActiveRecord
{
    const TABLE='chat_buffer';

    public $id;
    public $for_user;
    public $from_user;
    public $message;
    private $_created_on;

    public function getCreated_On()
    {
        if($this->_created_on === null)
            $this->_created_on = time();
        return $this->_created_on;
    }

    public function setCreated_On($value)
    {
        $this->_created_on = $value;
    }

    public static function finder($className=__CLASS__)
    {
        return parent::finder($className);
    }

    public function saveMessage()
    {
        foreach(ChatUserRecord::finder()->findAll() as $user)
        {
            $message = new self;
            $message->for_user = $user->username;
            $message->from_user = $this->from_user;
            $message->message = $this->message;
            $message->save();
            $history = new ChatHistoryRecord();
            $history->saveMessage($message);
            
            if($user->username == $this->from_user)
            {
                $user->last_activity = time(); //update the last activity;
                $user->save();
            }
        }
    }

    public function getUserMessages($user)
    {
        $content = '';
        foreach($this->findAll('for_user = ?', $user) as $message)
            $content .= $this->formatMessage($message);
        $this->deleteAll('for_user = ? OR created_on < ?',
                            $user, time() - 300); //5 min inactivity
        return $content;
    }

    public function getUserMessagesArray($data)
    {
        $username = trim($data['username']);
        $messages = array();
        foreach($this->findAll('for_user = ?', $username) as $message) {
            $messages[] = array(
                'from_user' => $message->from_user,
                'message' => $message->message,
                'created_on' => (int) $message->created_on
            );
        }

        $this->deleteAll('for_user = ? OR created_on < ?',
                            $username, time() - 300); //5 min inactivity
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
    
    protected function formatMessage($message)
    {
        $user = htmlspecialchars($message->from_user);
        $content = htmlspecialchars($message->message);
        return "<div class=\"message\"><strong>{$user}:</strong>"
                    ." <span>{$content}</span></div>";
    }
}
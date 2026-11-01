<?php
use Prado\Web\UI\TPage;

class Home extends TPage
{
    /*
    public function buttonClicked($sender,$param)
    {
        $db = $this->Application->Modules['db']->DbConnection;
        $name=$this->NameInput->Text;
        if(!empty($name))
            $this->GreetingLabel->Text="Cześć, ".THttpUtility::htmlEncode($name)."!";
        else
            $this->GreetingLabel->Text="Wpisz swoje imię w polu powyżej.";
    }
        */
    function processMessage($sender, $param)
    {
        if(strlen($this->userinput->Text) > 0)
        {
            $record = $this->createChatBufferRecord();
            $record->message = $this->userinput->Text;
            $record->from_user = $this->Application->User->Name;
            $record->saveMessage();
            $this->refreshUserList();
            $this->refreshMessageList();
            $this->userinput->Text = '';
            $this->CallbackClient->focus($this->userinput);
        }
    }

    protected function createChatBufferRecord()
    {
        return new ChatBufferRecord();
    }

    protected function refreshUserList()
    {
        $lastUpdate = $this->getViewState('userList','');
        $users = ChatUserRecord::finder()->getUserList();
        if($lastUpdate != $users)
        {
            $this->CallbackClient->update('users', $users);
            $this->setViewstate('userList', $users);
        }
    }

    protected function refreshMessageList()
    {
        //refresh the message list
        $finder = ChatBufferRecord::finder();
        $content = $finder->getUserMessages($this->Application->User->Name);
        if(strlen($content) > 0)
        {
            $anchor = (string)time();
            $content .= "<a href=\"#\" id=\"{$anchor}\"> </a>";
            $this->CallbackClient->appendContent("messages", $content);
            $this->CallbackClient->focus($anchor);
        }
    }

    function refresh($sender, $param)
    {
        $this->refreshUserList();
        $this->refreshMessageList();
    }

    public function onLoad($param)
    {
        $username = $this->Application->User->Name;
        if(!$this->Application->Modules['users']->usernameExists($username))
        {
            $auth = $this->Application->Modules['auth'];
            $auth->logout();

            //redirect to login page.
            $this->Response->Redirect($this->Service->ConstructUrl($auth->LoginPage));
        }
    }
}

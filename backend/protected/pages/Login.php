<?php
use Prado\Web\UI\TPage;

class Login extends TPage
{
    function checkUsername($sender, $param)
    {
        $manager = $this->Application->Modules['users'];
        if($manager->usernameExists($this->username->Text))
            $param->IsValid = false;
    }

    function createNewUser($sender, $param)
    {
        if($this->Page->IsValid)
        {
            $manager = $this->Application->Modules['users'];
            $manager->addNewUser($this->username->Text);

            //do manual login
            $user = $manager->getUser($this->username->Text);
            $auth = $this->Application->Modules['auth'];
            $auth->updateSessionUser($user);
            $this->Application->User = $user;

            $url = $this->Service->constructUrl($this->Service->DefaultPage);
            $this->Response->redirect($url);
        }
    }
}
<?php

use Prado\Web\UI\TPage;
use Prado\Web\THttpUtility;

class Users extends TPage
{
    public function onLoad($param)
    {
        parent::onLoad($param);

        $db = $this->Application->Modules['db']->DbConnection;
        $reader = $db->createCommand('SELECT name, email FROM users ORDER BY name')->query();
        $users = $reader->readAll();

        foreach ($users as &$user) {
            $user['name'] = THttpUtility::htmlEncode($user['name']);
            $user['email'] = THttpUtility::htmlEncode($user['email']);
        }
        unset($user);

        $this->UsersGrid->DataSource = $users;
        $this->UsersGrid->dataBind();
        $this->EmptyMessage->Visible = count($users) === 0;
    }
}
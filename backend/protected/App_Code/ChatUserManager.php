<?php

use Prado\Security\IUserManager;
use Prado\Security\TUser;
use Prado\TModule;

class ChatUserManager extends TModule implements IUserManager
{
    public function getGuestName()
    {
        return 'Guest';
    }

    public function getUser($username=null)
    {
        $user=new TUser($this);
        $user->setIsGuest(true);
        if($username !== null && $this->usernameExists($username))
        {
            $user->setIsGuest(false);
            $user->setName($username);
            $user->setRoles(array('normal'));
        }
        return $user;
    }

    public function addNewUser($username)
    {
        $user = new ChatUserRecord();
        $user->username = $username;
        $user->save();
    }

    public function usernameExists($username)
    {
        $finder = ChatUserRecord::finder();
        $record = $finder->findByUsername($username);
        return $record instanceof ChatUserRecord;
    }

    public function getUserFromCookie($cookie)
    {
        return null;
    }

    public function saveUserToCookie($cookie)
    {
    }

    public function validateUser($username,$password)
    {
        return $this->usernameExists($username);
    }
}
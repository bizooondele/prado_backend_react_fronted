<?php

use Prado\Data\ActiveRecord\TActiveRecord;

class User extends TActiveRecord
{
    const TABLE ="users";

    public $id;
    public $name;
    public $email;

    public function tableName()
    {
        return self::TABLE;
    }

    public function findAllUsers()
    {
        return User::finder()->findAll();
    }
}
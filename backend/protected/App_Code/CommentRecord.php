<?php

use Prado\Data\ActiveRecord\TActiveRecord;

class CommentRecord extends TActiveRecord
{
    const TABLE = 'comments';

    public $id;
    public $user_id;
    public $comment;

    public $user;

    public static $RELATIONS = [
        'user' => [self::BELONGS_TO, 'UserRecord', 'user_id'],
    ];

    public static function finder($className = __CLASS__)
    {
        return parent::finder($className);
    }
}

<?php

use Prado\Data\ActiveRecord\TActiveRecord;

class UserRecord extends TActiveRecord
{
    const TABLE = 'users';

    public $id;
    public $name;
    public $email;
    public $password;
    public $comments;
    public $status = 0; // INT column storing bit flags

    private static $flagMap = [
        'IS_ACTIVE'    => 1,
        'IS_MODERATOR' => 2,
        'IS_ADMIN'     => 4,
        'IS_PREMIUM'   => 8,
    ];

    public static $RELATIONS = array(
        'comments' => array(self::HAS_MANY, 'CommentRecord', 'user_id'),
    );

    public static function finder($className = __CLASS__)
    {
        return parent::finder($className);
    }

    public function getActiveFlagsForFrontend()
    {
        $activeFlags = [];
        foreach (self::$flagMap as $stringFlag => $bitValue) {
            if (((int) $this->status & $bitValue) === $bitValue) {
                $activeFlags[] = $stringFlag;
            }
        }
        return $activeFlags;
    }

    public function setFlagsFromFrontend(array $flags)
    {
        $newStatus = 0;
        foreach ($flags as $stringFlag) {
            if (isset(self::$flagMap[$stringFlag])) {
                $newStatus |= self::$flagMap[$stringFlag];
            }
        }
        $this->status = $newStatus;
    }
}

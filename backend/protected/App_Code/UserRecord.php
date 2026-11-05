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
    public $origin = 0;

    private static $flagMap = [
        'IS_ACTIVE'    => 1,
        'IS_MODERATOR' => 2,
        'IS_ADMIN'     => 4,
        'IS_PREMIUM'   => 8,
    ];

    public static $ORIGIN_FLAG_MAP= [
        'email' => 1,
        'google' => 2,
        'facebook' => 4
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

    public function getOriginString()
    {
        return self::getOriginStringFromValue($this->origin);
    }

    public static function getOriginStringFromValue($originValue)
    {
        $origin = array_search((int) $originValue, self::$ORIGIN_FLAG_MAP, true);

        return $origin === false ? null : $origin;
    }
}

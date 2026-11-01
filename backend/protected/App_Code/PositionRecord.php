<?php

use Prado\Data\ActiveRecord\TActiveRecord;

class PositionRecord extends TActiveRecord
{
    const TABLE = 'positions';

    public $id;
    public $code;
    public $name;

    public static $RELATIONS = array(
        'players' => array(self::MANY_TO_MANY, 'PlayerRecord', 'player_positions'),
    );

    public static function finder($className = __CLASS__)
    {
        return parent::finder($className);
    }
}


<?php

use Prado\Data\ActiveRecord\TActiveRecord;

class StadiumRecord extends TActiveRecord
{
    const TABLE = 'stadiums';

    public $id;
    public $name;
    public $city;
    public $state;
    public $capacity;

    public static $RELATIONS = array(
        'teams' => array(self::HAS_MANY, 'TeamRecord', 'stadium_id'),
        'games' => array(self::HAS_MANY, 'GameRecord', 'stadium_id'),
    );

    public static function finder($className = __CLASS__)
    {
        return parent::finder($className);
    }
}


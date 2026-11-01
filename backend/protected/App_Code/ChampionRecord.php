<?php

use Prado\Data\ActiveRecord\TActiveRecord;

class ChampionRecord extends TActiveRecord
{
    const TABLE = 'champions';

    public $id;
    public $season_year;
    public $team_id;

    public static $RELATIONS = array(
        'team' => array(self::BELONGS_TO, 'TeamRecord', 'team_id'),
    );

    public static function finder($className = __CLASS__)
    {
        return parent::finder($className);
    }
}


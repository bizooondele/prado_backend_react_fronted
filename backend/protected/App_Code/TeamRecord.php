<?php

use Prado\Data\ActiveRecord\TActiveRecord;

class TeamRecord extends TActiveRecord
{
    const TABLE = 'teams';

    public $id;
    public $external_id;
    public $stadium_id;
    public $city;
    public $name;
    public $abbreviation;
    public $conference;
    public $division;

    public static $RELATIONS = array(
        'stadium' => array(self::BELONGS_TO, 'StadiumRecord', 'stadium_id'),
        'coach' => array(self::HAS_ONE, 'CoachRecord', 'team_id'),
        'players' => array(self::HAS_MANY, 'PlayerRecord', 'team_id'),
        'home_games' => array(self::HAS_MANY, 'GameRecord', 'home_team_id'),
        'away_games' => array(self::HAS_MANY, 'GameRecord', 'away_team_id'),
        'championships' => array(self::HAS_MANY, 'ChampionRecord', 'team_id'),
    );

    public static function finder($className = __CLASS__)
    {
        return parent::finder($className);
    }
}


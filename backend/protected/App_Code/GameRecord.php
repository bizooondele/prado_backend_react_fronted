<?php

use Prado\Data\ActiveRecord\TActiveRecord;

class GameRecord extends TActiveRecord
{
    const TABLE = 'games';

    public $id;
    public $season;
    public $game_date;
    public $home_team_id;
    public $away_team_id;
    public $stadium_id;
    public $home_score;
    public $away_score;

    public static $RELATIONS = array(
        'home_team' => array(self::BELONGS_TO, 'TeamRecord', 'home_team_id'),
        'away_team' => array(self::BELONGS_TO, 'TeamRecord', 'away_team_id'),
        'stadium' => array(self::BELONGS_TO, 'StadiumRecord', 'stadium_id'),
        'game_stats' => array(self::HAS_MANY, 'GameStatRecord', 'game_id'),
    );

    public static function finder($className = __CLASS__)
    {
        return parent::finder($className);
    }
}


<?php

use Prado\Data\ActiveRecord\TActiveRecord;

class PlayerSeasonStatRecord extends TActiveRecord
{
    const TABLE = 'player_season_stats';

    public $id;
    public $player_id;
    public $season;
    public $team_id;
    public $games_played;
    public $minutes;
    public $points;
    public $assists;
    public $totalRebounds;

    public static $RELATIONS = array(
        'player' => array(self::BELONGS_TO, 'PlayerRecord', 'player_id'),
        'team' => array(self::BELONGS_TO, 'TeamRecord', 'team_id'),
    );

    public static function finder($className = __CLASS__)
    {
        return parent::finder($className);
    }
}


<?php

use Prado\Data\ActiveRecord\TActiveRecord;

class GameStatRecord extends TActiveRecord
{
    const TABLE = 'game_stats';

    public $id;
    public $game_id;
    public $player_id;
    public $team_id;
    public $minutes;
    public $points;
    public $assists;
    public $totalRebounds;
    public $was_starter;

    public static $RELATIONS = array(
        'game' => array(self::BELONGS_TO, 'GameRecord', 'game_id'),
        'player' => array(self::BELONGS_TO, 'PlayerRecord', 'player_id'),
        'team' => array(self::BELONGS_TO, 'TeamRecord', 'team_id'),
    );

    public static function finder($className = __CLASS__)
    {
        return parent::finder($className);
    }
}


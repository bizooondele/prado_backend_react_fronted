<?php

use Prado\Data\ActiveRecord\TActiveRecord;

class PlayerCareerStatRecord extends TActiveRecord
{
    const TABLE = 'player_career_stats';

    public $id;
    public $player_id;
    public $seasons_played;
    public $games_played;
    public $minutes;
    public $points;
    public $assists;
    public $totalRebounds;

    public static $RELATIONS = array(
        'player' => array(self::BELONGS_TO, 'PlayerRecord', 'player_id'),
    );

    public static function finder($className = __CLASS__)
    {
        return parent::finder($className);
    }
}


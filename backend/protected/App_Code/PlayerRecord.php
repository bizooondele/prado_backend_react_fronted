<?php

use Prado\Data\ActiveRecord\TActiveRecord;

class PlayerRecord extends TActiveRecord
{
    const TABLE = 'players';

    public $id;
    public $external_id;
    public $team_id;
    public $first_name;
    public $last_name;
    public $jersey_number;
    public $height_inches;
    public $weight_lbs;
    public $birth_date;
    public $college;
    public $experience_years;
    public $contract_type;

    public static $RELATIONS = array(
        'team' => array(self::BELONGS_TO, 'TeamRecord', 'team_id'),
        'positions' => array(self::MANY_TO_MANY, 'PositionRecord', 'player_positions'),
        'prior_teams' => array(self::MANY_TO_MANY, 'TeamRecord', 'player_prior_teams'),
        'season_stats' => array(self::HAS_MANY, 'PlayerSeasonStatRecord', 'player_id'),
        'career_stats' => array(self::HAS_ONE, 'PlayerCareerStatRecord', 'player_id'),
        'game_stats' => array(self::HAS_MANY, 'GameStatRecord', 'player_id'),
    );

    public static function finder($className = __CLASS__)
    {
        return parent::finder($className);
    }
}


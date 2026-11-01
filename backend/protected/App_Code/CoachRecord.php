<?php

use Prado\Data\ActiveRecord\TActiveRecord;

class CoachRecord extends TActiveRecord
{
    const TABLE = 'coaches';

    public $id;
    public $external_id;
    public $team_id;
    public $first_name;
    public $last_name;
    public $experience_years;

    public static $RELATIONS = array(
        'team' => array(self::BELONGS_TO, 'TeamRecord', 'team_id'),
        'prior_teams' => array(self::MANY_TO_MANY, 'TeamRecord', 'coach_prior_teams'),
    );

    public static function finder($className = __CLASS__)
    {
        return parent::finder($className);
    }
}


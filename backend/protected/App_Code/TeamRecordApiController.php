<?php

class TeamRecordApiController extends ApiResourceController
{
    public function getAll($data)
    {
        return TeamRecord::finder()->findAll();
    }

    public function getAllWithPlayers($data)
    {
        return TeamRecord::finder()->withPlayers()->findAll();
    }

    public function getOne($data)
    {
        $teamName = $this->requireString($data, 'teamname');
        $record = $this->requireRecord(TeamRecord::finder()->findByName($teamName), $teamName);

        return $record;
    }
    
    public function getOneWithPlayers($data)
    {
        $teamName = $this->requireString($data, 'teamname');
        $record = $this->requireRecord(TeamRecord::finder()->withPlayers()->findByName($teamName), $teamName);

        return $record;
    }

    public function save($data)
    {
        $teamName = $this->requireString($data, 'teamname');
        if (strlen($teamName) > 20) {
            throw new ApiRequestException(400, 'teamname must not exceed 20 characters.');
        }

        if (TeamRecord::finder()->findByName($teamName) !== null) {
            throw new ApiRequestException(409, 'Team already exists.');
        }

        $record = new TeamRecord();
        $record->teamname = $teamName;
        $record->save();

        return $record;
    }

    public function removeAll($data)
    {
        $deleted = TeamRecord::finder()->deleteAll();
        return array('deleted' => (int) $deleted);
    }

    private function toArray($record)
    {
        return array(
            'name' => $record->name,
            'city' => $record->city
        );
    }
}

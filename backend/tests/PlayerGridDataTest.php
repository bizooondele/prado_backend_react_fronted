<?php

require_once __DIR__ . '/../protected/App_Code/PlayerGridData.php';

class PlayerGridDataTest extends PHPUnit\Framework\TestCase
{
    public function testRowsIncludePositionsAndTotalPoints()
    {
        $player = (object) [
            'id' => 7,
            'team' => (object) ['name' => 'Team'],
            'first_name' => 'First',
            'last_name' => 'Last',
            'positions' => [(object) ['code' => 'G'], (object) ['code' => 'F']],
            'jersey_number' => '12',
            'game_stats' => [(object) ['points' => '5'], (object) ['points' => '9']],
        ];

        $this->assertSame([[
            'id' => 7,
            'team_name' => 'Team',
            'first_name' => 'First',
            'last_name' => 'Last',
            'position' => 'G, F',
            'jersey_number' => 12,
            'points' => 14,
        ]], PlayerGridData::rowsFromPlayers([$player]));
    }

    public function testRowsHandleMissingTeamAndEmptyRelations()
    {
        $player = (object) [
            'id' => 8,
            'team' => null,
            'first_name' => 'First',
            'last_name' => 'Last',
            'positions' => [],
            'jersey_number' => null,
            'game_stats' => [],
        ];

        $rows = PlayerGridData::rowsFromPlayers([$player]);
        $this->assertSame('', $rows[0]['team_name']);
        $this->assertSame('', $rows[0]['position']);
        $this->assertSame(0, $rows[0]['jersey_number']);
        $this->assertSame(0, $rows[0]['points']);
        $this->assertSame([], PlayerGridData::rowsFromPlayers([]));
    }
}

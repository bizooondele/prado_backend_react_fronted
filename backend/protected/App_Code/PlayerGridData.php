<?php

class PlayerGridData
{
    public static function getRows()
    {
        $players = PlayerRecord::finder()
            ->withTeam()
            ->withPositions()
            ->withGame_Stats()
            ->findAll();

        return self::rowsFromPlayers($players);
    }

    public static function rowsFromPlayers($players)
    {
        $rows = [];
        foreach ($players as $p) {
            $positions = [];
            foreach ($p->positions as $pos) {
                $positions[] = $pos->code;
            }
            $points = 0;
            foreach ($p->game_stats as $s) {
                $points += (int) $s->points;
            }
            $rows[] = [
                'id' => $p->id,
                'team_name' => $p->team ? $p->team->name : '',
                'first_name' => $p->first_name,
                'last_name' => $p->last_name,
                'position' => implode(', ', $positions),
                'jersey_number' => (int) $p->jersey_number,
                'points' => $points,
            ];
        }

        return $rows;
    }
}

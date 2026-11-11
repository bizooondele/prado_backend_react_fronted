<?php

use Phinx\Migration\AbstractMigration;

/**
 * Season/career stats, games and per-game player stats.
 * Season stats are totals keyed by (player_id, season) so history is preserved.
 * Game stats are only the games recorded here, not a player's full history.
 * Application code must ensure games.home_team_id <> games.away_team_id.
 */
final class CreateNbaStatsAndGameTables extends AbstractMigration
{
    private function addStatColumns($table)
    {
        return $table
            ->addColumn('games_played', 'integer', ['signed' => false, 'null' => true])
            ->addColumn('minutes', 'integer', ['signed' => false, 'null' => true])
            ->addColumn('points', 'integer', ['signed' => false, 'null' => true])
            ->addColumn('assists', 'integer', ['signed' => false, 'null' => true])
            ->addColumn('totalRebounds', 'integer', ['signed' => false, 'null' => true]);
    }

    public function change(): void
    {
        $season = $this->table('player_season_stats')
            ->addColumn('player_id', 'integer', ['signed' => false, 'null' => false])
            ->addColumn('season', 'string', ['limit' => 7, 'null' => false, 'comment' => 'e.g. 2026-27'])
            ->addColumn('team_id', 'integer', ['signed' => false, 'null' => true]);
        $this->addStatColumns($season)
            ->addIndex(['player_id', 'season'], ['unique' => true, 'name' => 'uq_player_season_stats_player_season'])
            ->addIndex(['team_id'])
            ->addForeignKey('player_id', 'players', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->addForeignKey('team_id', 'teams', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
            ->create();

        $career = $this->table('player_career_stats')
            ->addColumn('player_id', 'integer', ['signed' => false, 'null' => false])
            ->addColumn('seasons_played', 'integer', ['signed' => false, 'null' => true]);
        $this->addStatColumns($career)
            ->addIndex(['player_id'], ['unique' => true, 'name' => 'uq_player_career_stats_player'])
            ->addForeignKey('player_id', 'players', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->create();

        $this->table('games')
            ->addColumn('season', 'string', ['limit' => 7, 'null' => false])
            ->addColumn('game_date', 'date', ['null' => false])
            ->addColumn('home_team_id', 'integer', ['signed' => false, 'null' => false])
            ->addColumn('away_team_id', 'integer', ['signed' => false, 'null' => false])
            ->addColumn('stadium_id', 'integer', ['signed' => false, 'null' => true])
            ->addColumn('home_score', 'integer', ['signed' => false, 'null' => true])
            ->addColumn('away_score', 'integer', ['signed' => false, 'null' => true])
            ->addIndex(['season', 'game_date'])
            ->addIndex(['home_team_id'])
            ->addIndex(['away_team_id'])
            ->addIndex(['stadium_id'])
            ->addIndex(['game_date', 'home_team_id', 'away_team_id'], ['unique' => true, 'name' => 'uq_games_date_teams'])
            ->addForeignKey('home_team_id', 'teams', 'id', ['delete' => 'RESTRICT', 'update' => 'CASCADE'])
            ->addForeignKey('away_team_id', 'teams', 'id', ['delete' => 'RESTRICT', 'update' => 'CASCADE'])
            ->addForeignKey('stadium_id', 'stadiums', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
            ->create();

        $this->table('game_stats')
            ->addColumn('game_id', 'integer', ['signed' => false, 'null' => false])
            ->addColumn('player_id', 'integer', ['signed' => false, 'null' => false])
            ->addColumn('team_id', 'integer', ['signed' => false, 'null' => true])
            ->addColumn('minutes', 'integer', ['signed' => false, 'null' => true])
            ->addColumn('points', 'integer', ['signed' => false, 'null' => true])
            ->addColumn('assists', 'integer', ['signed' => false, 'null' => true])
            ->addColumn('totalRebounds', 'integer', ['signed' => false, 'null' => true])
            ->addColumn('was_starter', 'boolean', ['default' => false, 'null' => false])
            ->addIndex(['game_id', 'player_id'], ['unique' => true, 'name' => 'uq_game_stats_game_player'])
            ->addIndex(['player_id'])
            ->addIndex(['team_id'])
            ->addForeignKey('game_id', 'games', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->addForeignKey('player_id', 'players', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->addForeignKey('team_id', 'teams', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
            ->create();
    }
}

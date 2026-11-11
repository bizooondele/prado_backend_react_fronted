<?php

use Phinx\Migration\AbstractMigration;

/**
 * NBA players, their positions (M:N) and prior teams (M:N history).
 * Roster size is deliberately not constrained: NBA standard/two-way limits vary.
 */
final class CreateNbaPlayerTables extends AbstractMigration
{
    public function change(): void
    {
        $this->table('players')
            ->addColumn('external_id', 'integer', ['signed' => false, 'null' => true])
            ->addColumn('team_id', 'integer', ['signed' => false, 'null' => true])
            ->addColumn('first_name', 'string', ['limit' => 80, 'null' => false])
            ->addColumn('last_name', 'string', ['limit' => 80, 'null' => false])
            ->addColumn('jersey_number', 'string', ['limit' => 3, 'null' => true])
            ->addColumn('height_inches', 'integer', ['signed' => false, 'null' => true])
            ->addColumn('weight_lbs', 'integer', ['signed' => false, 'null' => true])
            ->addColumn('birth_date', 'date', ['null' => true])
            ->addColumn('college', 'string', ['limit' => 120, 'null' => true])
            ->addColumn('experience_years', 'integer', ['signed' => false, 'null' => true])
            ->addColumn('contract_type', 'string', ['limit' => 20, 'null' => true, 'comment' => 'standard, two_way, exhibit_10 ...; NULL = unknown'])
            ->addIndex(['external_id'], ['unique' => true, 'name' => 'uq_players_external_id'])
            ->addIndex(['team_id'])
            ->addIndex(['last_name', 'first_name'])
            ->addForeignKey('team_id', 'teams', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
            ->create();

        $this->table('player_positions', ['id' => false, 'primary_key' => ['player_id', 'position_id']])
            ->addColumn('player_id', 'integer', ['signed' => false, 'null' => false])
            ->addColumn('position_id', 'integer', ['signed' => false, 'null' => false])
            ->addIndex(['position_id'])
            ->addForeignKey('player_id', 'players', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->addForeignKey('position_id', 'positions', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->create();

        $this->table('player_prior_teams')
            ->addColumn('player_id', 'integer', ['signed' => false, 'null' => false])
            ->addColumn('team_id', 'integer', ['signed' => false, 'null' => false])
            ->addColumn('start_season', 'integer', ['signed' => false, 'null' => true])
            ->addColumn('end_season', 'integer', ['signed' => false, 'null' => true])
            ->addIndex(['player_id', 'team_id', 'start_season'], ['unique' => true, 'name' => 'uq_player_prior_teams_stint'])
            ->addIndex(['team_id'])
            ->addForeignKey('player_id', 'players', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->addForeignKey('team_id', 'teams', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->create();
    }
}

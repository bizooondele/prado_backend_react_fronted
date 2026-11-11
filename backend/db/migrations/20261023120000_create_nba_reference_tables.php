<?php

use Phinx\Migration\AbstractMigration;

/**
 * NBA reference data: stadiums, teams, coaches, positions.
 * Many teams may share one stadium; a coach is attached to at most one current team.
 */
final class CreateNbaReferenceTables extends AbstractMigration
{
    public function change(): void
    {
        $this->table('stadiums')
            ->addColumn('name', 'string', ['limit' => 120, 'null' => false])
            ->addColumn('city', 'string', ['limit' => 80, 'null' => false])
            ->addColumn('state', 'string', ['limit' => 40, 'null' => true])
            ->addColumn('capacity', 'integer', ['signed' => false, 'null' => true])
            ->addIndex(['name', 'city'], ['unique' => true, 'name' => 'uq_stadiums_name_city'])
            ->create();

        $this->table('teams')
            ->addColumn('external_id', 'integer', ['signed' => false, 'null' => true])
            ->addColumn('stadium_id', 'integer', ['signed' => false, 'null' => true])
            ->addColumn('city', 'string', ['limit' => 80, 'null' => false])
            ->addColumn('name', 'string', ['limit' => 80, 'null' => false])
            ->addColumn('abbreviation', 'string', ['limit' => 5, 'null' => false])
            ->addColumn('conference', 'string', ['limit' => 10, 'null' => false])
            ->addColumn('division', 'string', ['limit' => 20, 'null' => true])
            ->addIndex(['external_id'], ['unique' => true, 'name' => 'uq_teams_external_id'])
            ->addIndex(['abbreviation'], ['unique' => true, 'name' => 'uq_teams_abbreviation'])
            ->addIndex(['city', 'name'], ['unique' => true, 'name' => 'uq_teams_city_name'])
            ->addIndex(['stadium_id'])
            ->addForeignKey('stadium_id', 'stadiums', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
            ->create();

        $this->table('coaches')
            ->addColumn('external_id', 'integer', ['signed' => false, 'null' => true])
            ->addColumn('team_id', 'integer', ['signed' => false, 'null' => true])
            ->addColumn('first_name', 'string', ['limit' => 80, 'null' => false])
            ->addColumn('last_name', 'string', ['limit' => 80, 'null' => false])
            ->addColumn('experience_years', 'integer', ['signed' => false, 'null' => true])
            ->addIndex(['external_id'], ['unique' => true, 'name' => 'uq_coaches_external_id'])
            ->addIndex(['team_id'], ['unique' => true, 'name' => 'uq_coaches_team_id'])
            ->addForeignKey('team_id', 'teams', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
            ->create();

        $this->table('positions')
            ->addColumn('code', 'string', ['limit' => 5, 'null' => false])
            ->addColumn('name', 'string', ['limit' => 40, 'null' => false])
            ->addIndex(['code'], ['unique' => true, 'name' => 'uq_positions_code'])
            ->create();

        $this->table('champions')
            ->addColumn('season_year', 'integer', ['signed' => false, 'null' => false, 'comment' => 'Year the finals were won, e.g. 2025 for 2024-25'])
            ->addColumn('team_id', 'integer', ['signed' => false, 'null' => false])
            ->addIndex(['season_year'], ['unique' => true, 'name' => 'uq_champions_season_year'])
            ->addIndex(['team_id'])
            ->addForeignKey('team_id', 'teams', 'id', ['delete' => 'RESTRICT', 'update' => 'CASCADE'])
            ->create();

        $this->table('coach_prior_teams')
            ->addColumn('coach_id', 'integer', ['signed' => false, 'null' => false])
            ->addColumn('team_id', 'integer', ['signed' => false, 'null' => false])
            ->addColumn('start_season', 'integer', ['signed' => false, 'null' => true])
            ->addColumn('end_season', 'integer', ['signed' => false, 'null' => true])
            ->addIndex(['coach_id', 'team_id', 'start_season'], ['unique' => true, 'name' => 'uq_coach_prior_teams_stint'])
            ->addIndex(['team_id'])
            ->addForeignKey('coach_id', 'coaches', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->addForeignKey('team_id', 'teams', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->create();
    }
}

<?php

use Phinx\Seed\AbstractSeed;

/**
 * Seeds the 30 current NBA teams with stadiums, conference/division, head coaches,
 * positions and the 2026-27 rosters. Safe to re-run: every row is upserted on a
 * natural/external key and no existing data is deleted.
 *
 * Sources (data in db/seeds/data/nba_2026_27.php, observed 2026-10-23, snapshot 2026-10-23T11:09:48Z):
 *  - Teams, coaches, players, birth dates, heights (inches), weights (lbs), colleges,
 *    experience and positions: ESPN public JSON endpoints
 *    https://site.api.espn.com/apis/site/v2/sports/basketball/nba/teams and
 *    https://site.api.espn.com/apis/site/v2/sports/basketball/nba/teams/{id}/roster
 *    (the endpoint labels the roster season "2026-27"). `external_id` columns hold ESPN ids.
 *  - Conference/division: static NBA alignment. Stadiums: curated list of each team's
 *    current home arena (ESPN venue data was stale, e.g. old Oakland/Sacramento arenas);
 *    verify arena naming rights before relying on them.
 *
 * Limitations: ESPN exposes only G/F/C positions and no contract type, so
 * players.contract_type (standard / two-way) is left NULL (unknown) rather than guessed.
 * Rosters are a point-in-time snapshot and change with trades/waivers. No season or
 * career statistics, games or champions are seeded because no sourced values were
 * collected; those tables are left empty.
 */
final class NbaSeeder extends AbstractSeed
{
    public function run(): void
    {
        $data = require __DIR__ . '/data/nba_2026_27.php';
        $pdo = $this->getAdapter()->getConnection();
        // Phinx already wraps run() in a transaction (Environment::executeSeed), so do not start another.
        try {
            $this->seed($pdo, $data);
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
        $this->getOutput()->writeln('NBA seed complete (rosters as of ' . $data['as_of'] . ', season ' . $data['season'] . ').');
    }

    private function seed(\PDO $pdo, array $data): void
    {
        $stmt = $pdo->prepare('INSERT INTO positions (code, name) VALUES (?, ?) ON DUPLICATE KEY UPDATE name = VALUES(name)');
        foreach ($data['positions'] as $row) {
            $stmt->execute($row);
        }

        $stmt = $pdo->prepare('INSERT INTO stadiums (name, city, state) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE state = VALUES(state)');
        foreach ($data['stadiums'] as $row) {
            $stmt->execute($row);
        }

        $stadiumIds = $this->lookup($pdo, "SELECT CONCAT(name, '|', city), id FROM stadiums");
        $stmt = $pdo->prepare(
            'INSERT INTO teams (external_id, abbreviation, city, name, conference, division, stadium_id) VALUES (?, ?, ?, ?, ?, ?, ?) '
            . 'ON DUPLICATE KEY UPDATE abbreviation = VALUES(abbreviation), city = VALUES(city), name = VALUES(name), '
            . 'conference = VALUES(conference), division = VALUES(division), stadium_id = VALUES(stadium_id)'
        );
        foreach ($data['teams'] as $row) {
            $stmt->execute(array($row[0], $row[1], $row[2], $row[3], $row[4], $row[5], $stadiumIds[$row[6] . '|' . $row[7]]));
        }

        $teamIds = $this->lookup($pdo, 'SELECT external_id, id FROM teams WHERE external_id IS NOT NULL');

        // Release seeded coaches first so a coach moving teams cannot hit the one-coach-per-team unique key.
        $pdo->exec('UPDATE coaches SET team_id = NULL WHERE external_id IS NOT NULL');
        $stmt = $pdo->prepare(
            'INSERT INTO coaches (external_id, first_name, last_name, experience_years, team_id) VALUES (?, ?, ?, ?, ?) '
            . 'ON DUPLICATE KEY UPDATE first_name = VALUES(first_name), last_name = VALUES(last_name), '
            . 'experience_years = VALUES(experience_years), team_id = VALUES(team_id)'
        );
        foreach ($data['coaches'] as $row) {
            $stmt->execute(array($row[0], $row[1], $row[2], $row[3], $teamIds[$row[4]]));
        }

        $positionIds = $this->lookup($pdo, 'SELECT code, id FROM positions');
        $stmt = $pdo->prepare(
            'INSERT INTO players (external_id, first_name, last_name, team_id, jersey_number, height_inches, weight_lbs, birth_date, college, experience_years) '
            . 'VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?) '
            . 'ON DUPLICATE KEY UPDATE first_name = VALUES(first_name), last_name = VALUES(last_name), team_id = VALUES(team_id), '
            . 'jersey_number = VALUES(jersey_number), height_inches = VALUES(height_inches), weight_lbs = VALUES(weight_lbs), '
            . 'birth_date = VALUES(birth_date), college = VALUES(college), experience_years = VALUES(experience_years)'
        );
        $playerIds = $pdo->prepare('SELECT id FROM players WHERE external_id = ?');
        $pos = $pdo->prepare('INSERT IGNORE INTO player_positions (player_id, position_id) VALUES (?, ?)');
        foreach ($data['players'] as $row) {
            $stmt->execute(array($row[0], $row[1], $row[2], $teamIds[$row[3]], $row[4], $row[5], $row[6], $row[7], $row[8], $row[9]));
            if ($row[10] !== null && isset($positionIds[$row[10]])) {
                $playerIds->execute(array($row[0]));
                $pos->execute(array($playerIds->fetchColumn(), $positionIds[$row[10]]));
            }
        }
    }

    private function lookup(\PDO $pdo, $sql)
    {
        $map = array();
        foreach ($pdo->query($sql)->fetchAll(\PDO::FETCH_NUM) as $row) {
            $map[$row[0]] = (int) $row[1];
        }
        return $map;
    }
}


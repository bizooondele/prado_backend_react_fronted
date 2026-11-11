<?php

use Phinx\Seed\AbstractSeed;

/**
 * Seeds game_stats for game_id = 1 from data/mecz_1_statystyki_graczy.csv.
 * - was_starter = 1 when the CSV "position" is filled in.
 * - Players are matched by first_name + last_name (accent/case-insensitive collation).
 * - Rows without minutes (DNP / inactive) are skipped; minutes are rounded to whole minutes.
 * - Unmatched players are reported and skipped. Safe to re-run (upsert on game_id + player_id).
 * Requires game id 1 and the NBA teams/players (NbaSeeder) to exist.
 */
final class GameStatsSeeder extends AbstractSeed
{
    private const GAME_ID = 1;

    public function run(): void
    {
        $pdo = $this->getAdapter()->getConnection();

        $handle = fopen(__DIR__ . '/data/mecz_1_statystyki_graczy.csv', 'r');
        if ($handle === false) {
            throw new \RuntimeException('Cannot open mecz_1_statystyki_graczy.csv');
        }
        $header = fgetcsv($handle);
        if (isset($header[0])) {
            $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]);
        }

        $findPlayer = $pdo->prepare('SELECT id FROM players WHERE first_name = ? AND last_name = ?');
        $findTeam = $pdo->prepare('SELECT id FROM teams WHERE abbreviation = ?');
        $upsert = $pdo->prepare(
            'INSERT INTO game_stats (game_id, player_id, team_id, minutes, points, assists, totalRebounds, was_starter) '
            . 'VALUES (?, ?, ?, ?, ?, ?, ?, ?) '
            . 'ON DUPLICATE KEY UPDATE team_id = VALUES(team_id), minutes = VALUES(minutes), points = VALUES(points), '
            . 'assists = VALUES(assists), totalRebounds = VALUES(totalRebounds), was_starter = VALUES(was_starter)'
        );

        $inserted = 0;
        while (($line = fgetcsv($handle)) !== false) {
            if (count($line) !== count($header)) {
                continue;
            }
            $row = array_combine($header, $line);
            if (trim($row['minutes']) === '') {
                continue;
            }

            $findPlayer->execute(array($row['firstName'], $row['familyName']));
            $playerId = $findPlayer->fetchColumn();
            if ($playerId === false) {
                $this->getOutput()->writeln('<comment>Player not found: ' . $row['firstName'] . ' ' . $row['familyName'] . '</comment>');
                continue;
            }

            $findTeam->execute(array($row['teamTricode']));
            $teamId = $findTeam->fetchColumn();

            $parts = explode(':', $row['minutes']);
            $minutes = (int) $parts[0] + ((int) (isset($parts[1]) ? $parts[1] : 0) >= 30 ? 1 : 0);

            $upsert->execute(array(
                self::GAME_ID,
                $playerId,
                $teamId === false ? null : $teamId,
                $minutes,
                (int) $row['points'],
                (int) $row['assists'],
                (int) $row['reboundsTotal'],
                trim($row['position']) !== '' ? 1 : 0,
            ));
            $inserted++;
        }
        fclose($handle);

        $this->getOutput()->writeln('game_stats seeded for game ' . self::GAME_ID . ': ' . $inserted . ' rows.');
    }
}
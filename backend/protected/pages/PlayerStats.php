<?php
use Prado\Web\UI\TPage;

class PlayerStats extends TPage
{
    public function onLoad($param)
    {
        parent::onLoad($param);

        if (!$this->IsPostBack) {
            $this->GameList->DataSource = GameRecord::finder()
            ->withGame_Stats()
            ->withStadium()
            ->withHome_Team()
            ->withAway_Team()
            ->findAll();
            //var_dump($this->GameList->DataSource);
            $this->GameList->dataBind();
        }
    }

    public function teamLabel($game)
    {
        $label = $game->game_date . ' ' . $game->home_team->name . ' VS ' . $game->away_team->name . ' @ ' . $game->stadium->name;
        return $label . ' | Score: ' . $game->home_score . '-' . $game->away_score;
    }

    public function showPlayerDetails($sender, $param)
    {
        $stat = GameStatRecord::finder()->withPlayer()->findByPk($sender->CommandParameter);
        if ($stat === null) {
            $this->PlayerDetails->Text = 'Player not found.';
            return;
        }

        $e = fn($v) => htmlspecialchars((string) $v);
        $this->PlayerDetails->Text = '<b>' . $e($stat->player->first_name . ' ' . $stat->player->last_name) . '</b><br/>'
            . 'Team: ' . $e($stat->player->team->name) . '<br/>'
            . 'minutes played: ' . $e($stat->minutes) . '<br/>'
            . 'points: ' . $e($stat->points) . '<br/>'
            . 'assists: ' . $e($stat->assists) . '<br/>'
            . 'rebounds: ' . $e($stat->totalRebounds) . '<br/>'
            . 'was starter: ' . $e($stat->was_starter) . '<br/>';
    }
}
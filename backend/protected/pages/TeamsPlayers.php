<?php
use Prado\Web\UI\TPage;

class TeamsPlayers extends TPage
{
    public function onLoad($param)
    {
        parent::onLoad($param);

        if (!$this->IsPostBack) {
            $this->TeamList->DataSource = TeamRecord::finder()
            //->with('players', 'championships', 'coach') //not working this way
            ->withPlayers()->withChampionships()->withCoach()
            ->findAll();
            //var_dump($this->TeamList->DataSource);
            $this->TeamList->dataBind();
        }
    }

    public function teamLabel($team)
    {
        $label = $team->city . ' ' . $team->name;
        if ($team->coach !== null) {
            $label .= ' | Coach: ' . $team->coach->first_name . ' ' . $team->coach->last_name;
        }
        return $label . ' | Championships: ' . count($team->championships ?? []);
    }

    public function showPlayerDetails($sender, $param)
    {
        $player = PlayerRecord::finder()->findByPk($sender->CommandParameter);
        if ($player === null) {
            $this->PlayerDetails->Text = 'Player not found.';
            return;
        }

        $e = fn($v) => htmlspecialchars((string) $v);
        $this->PlayerDetails->Text = '<b>' . $e($player->first_name . ' ' . $player->last_name) . '</b><br/>'
            . 'Jersey: ' . $e($player->jersey_number) . '<br/>'
            . 'Height: ' . $e($player->height_inches) . ' in<br/>'
            . 'Weight: ' . $e($player->weight_lbs) . ' lbs<br/>'
            . 'Birth date: ' . $e($player->birth_date) . '<br/>'
            . 'College: ' . $e($player->college) . '<br/>'
            . 'Experience: ' . $e($player->experience_years) . ' years<br/>'
            . 'Contract: ' . $e($player->contract_type);
    }
}
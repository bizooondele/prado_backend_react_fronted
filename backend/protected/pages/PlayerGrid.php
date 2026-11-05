<?php
use Prado\Web\UI\TPage;

class PlayerGrid extends TPage
{
    public function onLoad($param)
    {
        parent::onLoad($param);
        if (!$this->IsPostBack) {
            $this->Grid->PageSize = (int) $this->PageSize->SelectedValue;
            $this->bindGrid();
        }
    }

    protected function getRows()
    {
        $players = PlayerRecord::finder()
            ->withTeam()
            ->withPositions()
            ->withGame_Stats()
            ->findAll();

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

        $sort = $this->getViewState('SortExpression', 'team_name');
        $desc = $this->getViewState('SortDesc', false);
        if (!isset($rows[0][$sort])) {
            $sort = 'team_name';
        }
        usort($rows, function ($a, $b) use ($sort, $desc) {
            $c = is_int($a[$sort]) ? $a[$sort] <=> $b[$sort] : strcasecmp($a[$sort], $b[$sort]);
            return $desc ? -$c : $c;
        });
        return $rows;
    }

    protected function bindGrid()
    {
        $this->Grid->DataSource = $this->getRows();
        $this->Grid->dataBind();
    }

    // Effective state = per-row override if present, otherwise the "all" flag.
    protected function isChecked($id)
    {
        $overrides = $this->getViewState('Overrides', []);
        return array_key_exists($id, $overrides) ? $overrides[$id] : (bool) $this->getViewState('AllChecked', false);
    }

    public function itemDataBound($sender, $param)
    {
        $item = $param->Item;
        $type = $item->ItemType;
        if ($type === 'Header') {
            $overrides = $this->getViewState('Overrides', []);
            $item->findControl('HeaderCheck')->Checked = $this->getViewState('AllChecked', false)
                && !in_array(false, $overrides, true);
        } elseif ($type === 'Item' || $type === 'AlternatingItem') {
            $checked = $this->isChecked($item->Data['id']);
            $item->findControl('RowCheck')->Checked = $checked;
            if ($checked) {
                $item->Cells[1]->Text = '';
            }
        }
    }

    public function headerToggled($sender, $param)
    {
        $this->setViewState('AllChecked', (bool) $sender->Checked);
        $this->setViewState('Overrides', []);
        $this->bindGrid();
    }

    public function rowToggled($sender, $param)
    {
        $overrides = $this->getViewState('Overrides', []);
        $overrides[(int) $sender->Value] = (bool) $sender->Checked;
        $this->setViewState('Overrides', $overrides);
        $this->bindGrid();
    }

    public function sortGrid($sender, $param)
    {
        $expr = $param->SortExpression;
        if ($this->getViewState('SortExpression') === $expr) {
            $this->setViewState('SortDesc', !$this->getViewState('SortDesc', false));
        } else {
            $this->setViewState('SortExpression', $expr);
            $this->setViewState('SortDesc', false);
        }
        $this->Grid->CurrentPageIndex = 0;
        $this->bindGrid();
    }

    public function pageChanged($sender, $param)
    {
        $this->Grid->CurrentPageIndex = $param->NewPageIndex;
        $this->bindGrid();
    }

    public function pageSizeChanged($sender, $param)
    {
        $this->Grid->PageSize = (int) $this->PageSize->SelectedValue;
        $this->Grid->CurrentPageIndex = 0;
        $this->bindGrid();
    }
}

<?php
use Prado\Web\UI\TPage;

class StadiumsPage extends TPage
{
    public function onLoad($param)
    {
        parent::onLoad($param);

        if (!$this->IsPostBack) {
            $this->StadiumList->DataSource = StadiumRecord::finder()
                ->withTeams()
                ->findAll();
            $this->StadiumList->dataBind();
        }
    }
}
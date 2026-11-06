<?php

use Prado\Web\UI\TPage;

class PlayerGridDevExtreme extends TPage
{
    public function getRowsJson()
    {
        return json_encode(
            PlayerGridData::getRows(),
            JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR
        );
    }
}

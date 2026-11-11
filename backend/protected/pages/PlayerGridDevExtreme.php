<?php

use Prado\Web\UI\TPage;

class PlayerGridDevExtreme extends TPage
{
    public function getTestApiPathJson()
    {
        $scriptUrl = $this->getRequest()->getApplicationUrl();
        $basePath = str_replace('\\', '/', dirname($scriptUrl));
        $basePath = $basePath === '.' ? '' : rtrim($basePath, '/');

        return json_encode(
            $basePath . '/api/test/post-json',
            JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR
        );
    }

    public function getRowsJson()
    {
        return json_encode(
            PlayerGridData::getRows(),
            JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR
        );
    }
}

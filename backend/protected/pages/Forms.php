<?php
namespace Application\Pages;

use Prado\Web\UI\TPage;

class Forms extends TPage
{
    /**
     * Page initialization - fires before rendering
     */
    public function onInit($param)
    {
        parent::onInit($param);
        // Initialize page state, load data, etc.
    }
    /**
     * Handles synchronous button click - full page refresh
     */
    public function updateDateTime($sender, $param)
    {
        $this->DateTimeDisplay->Text = sprintf(
            'Updated: %s (Full Postback)',
            date('Y-m-d H:i:s')
        );
    }
    
    /**
     * Handles AJAX callback - partial update only
     */
    public function loadServerTime($sender, $param)
    {
        $this->DateTimeDisplay->Text = sprintf(
            'Server Time: %s (AJAX Update)',
            date('Y-m-d H:i:s.u')
        );
        
        // Optionally add callback parameters
        $param->setResponseData([
            'timestamp' => time(),
            'timezone' => date_default_timezone_get()
        ]);
    }
}
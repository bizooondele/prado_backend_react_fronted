<?php
namespace Application\Components;

use Prado\Web\UI\TTemplateControl;

class StatusCard extends TTemplateControl
{
    private $_title;
    private $_status;
    private $_message;
    
    /**
     * Title property with getter/setter
     */
    public function getTitle()
    {
        return $this->_title;
    }
    
    public function setTitle($value)
    {
        $this->_title = $value;
    }
    
    /**
     * Status property (e.g., 'success', 'warning', 'error')
     */
    public function getStatus()
    {
        return $this->_status;
    }
    
    public function setStatus($value)
    {
        $this->_status = $value;
    }
    
    /**
     * Message property
     */
    public function getMessage()
    {
        return $this->_message;
    }
    
    public function setMessage($value)
    {
        $this->_message = $value;
    }
    
    /**
     * Render the component with current properties
     */
    public function onPreRender($param)
    {
        parent::onPreRender($param);
        
        // Ensure default values
        if (empty($this->_title)) {
            $this->_title = 'System Status';
        }
        
        if (empty($this->_status)) {
            $this->_status = 'info';
        }
        
        if (empty($this->_message)) {
            $this->_message = 'All systems operational.';
        }
    }
}
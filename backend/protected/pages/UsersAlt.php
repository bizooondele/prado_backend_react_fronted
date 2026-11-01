<?php
use Prado\Web\UI\TPage;

class UsersAlt extends TPage
{
   public function onLoad($param)
    {
        parent::onLoad($param);
        
        if (!$this->IsPostBack) {
            // Simulate database query result
            $users = [
                ['id' => 1, 'name' => 'Alice Johnson', 'email' => 'alice@example.com'],
                ['id' => 2, 'name' => 'Bob Smith', 'email' => 'bob@example.com'],
                ['id' => 3, 'name' => 'Carol White', 'email' => 'carol@example.com'],
            ];
            
            $this->UserList->DataSource = $users;
            $this->UserList->dataBind();
        }
    }

    public function onUserItemCreated($sender, $param)
    {
        // Access individual item during creation
        $item = $param->Item;
        if ($item->ItemType === 'Item' || $item->ItemType === 'AlternatingItem') {
            // Customize individual items if needed
        }
    }

    public function viewUserDetails($sender, $param)
    {
        $userId = $param->CommandParameter;
        // Handle user detail view logic
        $this->UserDetails->Text = "Viewing details for user ID: " . $userId;
    }
}
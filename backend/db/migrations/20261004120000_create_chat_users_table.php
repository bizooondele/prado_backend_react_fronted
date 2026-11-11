<?php

use Phinx\Migration\AbstractMigration;

final class CreateChatUsersTable extends AbstractMigration
{
    public function change(): void
    {
        $this->table('chat_users', ['id' => false, 'primary_key' => ['username']])
            ->addColumn('username', 'string', ['limit' => 20, 'null' => false])
            ->addColumn('last_activity', 'integer', ['default' => 0, 'null' => false])
            ->create();
    }
}

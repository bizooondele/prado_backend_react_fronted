<?php

use Phinx\Migration\AbstractMigration;

final class CreateChatBufferTable extends AbstractMigration
{
    public function change(): void
    {
        $this->table('chat_buffer')
            ->addColumn('for_user', 'string', ['limit' => 20, 'null' => false])
            ->addColumn('from_user', 'string', ['limit' => 20, 'null' => false])
            ->addColumn('message', 'text', ['null' => false])
            ->addColumn('created_on', 'integer', ['default' => 0, 'null' => false])
            ->create();
    }
}

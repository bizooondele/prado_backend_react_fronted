<?php

use Phinx\Migration\AbstractMigration;

final class CreateCommentsTable extends AbstractMigration
{
    public function change(): void
    {
        $this->table('comments')
            ->addColumn('user_id', 'integer', ['null' => false])
            ->addColumn('comment', 'string', ['limit' => 255, 'null' => false])
            ->create();
    }
}

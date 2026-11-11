<?php

use Phinx\Migration\AbstractMigration;

final class AddStatusToUsers extends AbstractMigration
{
    public function up(): void
    {
        $this->table('users')
            ->addColumn('status', 'integer', ['default' => 0, 'null' => false, 'signed' => false])
            ->update();
    }

    public function down(): void
    {
        $this->table('users')->removeColumn('status')->update();
    }
}

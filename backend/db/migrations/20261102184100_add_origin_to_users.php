<?php

use Phinx\Migration\AbstractMigration;

final class AddOriginToUsers extends AbstractMigration
{
    public function up(): void
    {
        $this->table('users')
            ->addColumn('origin', 'integer', ['default' => 0, 'null' => false, 'signed' => false])
            ->update();
    }

    public function down(): void
    {
        $this->table('users')->removeColumn('origin')->update();
    }
}

<?php

use Phinx\Migration\AbstractMigration;

final class AddIdToUsersTable extends AbstractMigration
{
    public function up(): void
    {
        if ($this->table('users')->hasColumn('id')) {
            return;
        }
        $this->execute('ALTER TABLE users ADD COLUMN id INT NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST');
    }

    public function down(): void
    {
        if ($this->table('users')->hasColumn('id')) {
            $this->execute('ALTER TABLE users DROP COLUMN id');
        }
    }
}

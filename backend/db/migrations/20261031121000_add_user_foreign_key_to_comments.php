<?php

use Phinx\Migration\AbstractMigration;

final class AddUserForeignKeyToComments extends AbstractMigration
{
    public function up(): void
    {
        $idType = $this->fetchRow(
            "SELECT COLUMN_TYPE AS t FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'id'"
        )['t'];
        $tables = $this->fetchAll(
            "SELECT TABLE_NAME AS n, ENGINE AS e FROM information_schema.TABLES
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME IN ('users', 'comments')"
        );
        foreach ($tables as $row) {
            if (strcasecmp($row['e'], 'InnoDB') !== 0) {
                $this->execute('ALTER TABLE `' . $row['n'] . '` ENGINE = InnoDB');
            }
        }

        $this->execute("ALTER TABLE comments MODIFY user_id $idType NOT NULL");
        $this->execute(
            'ALTER TABLE comments ADD CONSTRAINT fk_comments_user_id FOREIGN KEY (user_id)
             REFERENCES users (id) ON DELETE CASCADE ON UPDATE CASCADE'
        );
    }

    public function down(): void
    {
        $this->execute('ALTER TABLE comments DROP FOREIGN KEY fk_comments_user_id');
    }
}

<?php

use Phinx\Db\Adapter\MysqlAdapter;
use Phinx\Migration\AbstractMigration;

final class CreateRawSensorDataTable extends AbstractMigration
{
    public function change(): void
    {
        $this->table('raw_sensor_data')
            ->addColumn('aid', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('eid', 'integer', ['null' => false])
            ->addColumn('data', 'text', ['limit' => MysqlAdapter::TEXT_LONG, 'null' => false])
            ->addColumn('creation_time', 'datetime', ['null' => false])
            ->create();
    }
}

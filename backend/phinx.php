<?php

$env = static function ($name, $default) {
    $value = getenv($name);
    return $value === false ? $default : $value;
};

return array(
    'paths' => array(
        'migrations' => '%%PHINX_CONFIG_DIR%%/db/migrations',
        'seeds' => '%%PHINX_CONFIG_DIR%%/db/seeds'
    ),
    'environments' => array(
        'default_migration_table' => 'phinxlog',
        'default_environment' => 'development',
        'development' => array(
            'adapter' => 'mysql',
            'host' => $env('PHINX_DB_HOST', 'localhost'),
            'name' => $env('PHINX_DB_NAME', 'prado'),
            'user' => $env('PHINX_DB_USER', 'admin'),
            'pass' => $env('PHINX_DB_PASSWORD', 'zenek_123'),
            'port' => $env('PHINX_DB_PORT', '3306'),
            'charset' => 'utf8mb4'
        )
    ),
    'version_order' => 'creation'
);

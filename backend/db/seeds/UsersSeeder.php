<?php

use Phinx\Seed\AbstractSeed;

final class UsersSeeder extends AbstractSeed
{
    public function run(): void
    {
        $this->table('users')->insert([
            [
                'name' => 'Alice Johnson',
                'email' => 'alice@example.com',
                'password' => password_hash('ChangeMe123!', PASSWORD_DEFAULT),
            ],
            [
                'name' => 'Bob Smith',
                'email' => 'bob@example.com',
                'password' => password_hash('ChangeMe123!', PASSWORD_DEFAULT),
            ],
        ])->saveData();
    }
}
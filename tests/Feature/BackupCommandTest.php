<?php

namespace Tests\Feature;

use Tests\TestCase;

class BackupCommandTest extends TestCase
{
    public function test_backup_refuses_non_mysql_connection(): void
    {
        config(['database.default' => 'sqlite']);

        $this->artisan('db:backup')
            ->expectsOutputToContain('MySQL only')
            ->assertExitCode(1);
    }
}

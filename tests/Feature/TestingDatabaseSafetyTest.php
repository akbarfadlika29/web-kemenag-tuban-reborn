<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TestingDatabaseSafetyTest extends TestCase
{
    public function test_phpunit_uses_dedicated_testing_database(): void
    {
        $this->assertSame(
            'pgsql',
            config('database.default')
        );

        $this->assertSame(
            'web_ppid_testing',
            DB::connection()->getDatabaseName()
        );
    }
}
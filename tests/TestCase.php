<?php

namespace Tests;

use Faker\Factory;
use Faker\Generator;
use PHPUnit\Framework\TestCase as BaseTestCase;
use Sphp\Core\Database;

class TestCase extends BaseTestCase
{
    protected ?Database $db = null;
    protected ?Generator $faker = null;

    protected function setUp(): void
    {
        parent::setUp();

        $config = [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'username' => '',
            'password' => ''
        ];

        $this->db = new Database($config);
        $this->faker = Factory::create();

        // Run migrations
        $this->migrate();
    }

    protected function tearDown(): void
    {
        $this->db = null;
        $this->faker = null;

        parent::tearDown();
    }

    private function migrate(): void
    {
        $migrations = [
            '20250506082420_users.sql',
            '20260126143038_user_tokens.sql'
        ];

        foreach ($migrations as $migration) {
            $sql = file_get_contents(__DIR__ . '/../app/Database/' . $migration);
            $this->db->query($sql);
        }
    }
}

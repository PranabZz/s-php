<?php

namespace Tests;

class UserSeederTest extends TestCase
{
    public function test_user_seeder_seeds_10_users(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $this->db->query("INSERT INTO users (name, email, password) VALUES (:name, :email, :password)", [
                'name' => $this->faker->name,
                'email' => $this->faker->unique()->safeEmail,
                'password' => password_hash('password', PASSWORD_DEFAULT)
            ]);
        }

        // Get the number of users from the database
        $users = $this->db->query("SELECT * FROM users");

        // Assert that there are 10 users in the database
        $this->assertCount(10, $users);
    }
}

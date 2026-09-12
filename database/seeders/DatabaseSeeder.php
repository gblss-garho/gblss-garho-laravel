<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->create([
            'name' => 'GBLSS Admin',
            'email' => 'wariskatyar2015@gmail.com',
            'role' => User::ROLE_ADMIN,
        ]);
    }
}

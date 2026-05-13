<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::create([
            'name'     => 'test',
            'email'    => 'test@test.com',
            'password' => Hash::make('1234'),
            'role'     => 'submitter',
        ]);

        User::create([
        'name'     => 'approver',
        'email'    => 'approver@test.com',
        'password' => Hash::make('1234'),
        'role'     => 'approver',
    ]);
    }
}
